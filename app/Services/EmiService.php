<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanEmiSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * EMI Engine — handles all interest and schedule calculations for the LMS.
 *
 * Supports:
 *   - Flat Rate
 *   - Reducing Balance (Monthly)
 *   - Reducing Balance (Daily)
 *   - Grace Period (no EMI period)
 *   - Moratorium (interest-only period)
 */
class EmiService
{
    /**
     * Calculate EMI amount given loan parameters.
     * Returns the monthly EMI as a float.
     */
    public function calculateEmi(
        float  $principal,
        float  $annualRatePercent,
        int    $tenureMonths,
        string $method = 'reducing',
        int    $moratoriumMonths = 0
    ): float {
        // Actual EMI-bearing tenure
        $emiTenure = $tenureMonths - $moratoriumMonths;

        if ($emiTenure <= 0) {
            return 0.0;
        }

        return match ($method) {
            'flat'           => $this->calculateFlatEmi($principal, $annualRatePercent, $tenureMonths, $emiTenure),
            'reducing'       => $this->calculateReducingEmi($principal, $annualRatePercent, $emiTenure),
            'reducing_daily' => $this->calculateReducingEmi($principal, $annualRatePercent, $emiTenure), // same formula; daily compound done in schedule
            default          => $this->calculateReducingEmi($principal, $annualRatePercent, $emiTenure),
        };
    }

    /**
     * Flat Rate EMI = (P + Total Interest) / Total EMI Count
     * Total Interest = P * R * T (where R is annual rate, T is in years)
     */
    protected function calculateFlatEmi(
        float $principal,
        float $annualRatePercent,
        int   $totalTenure,
        int   $emiTenure
    ): float {
        $totalInterest = $principal * ($annualRatePercent / 100) * ($totalTenure / 12);
        return round(($principal + $totalInterest) / $emiTenure, 2);
    }

    /**
     * Reducing Balance EMI using standard EMI formula:
     * EMI = P * r * (1+r)^n / ((1+r)^n - 1)
     * where r = monthly rate, n = tenure in months
     */
    protected function calculateReducingEmi(
        float $principal,
        float $annualRatePercent,
        int   $tenureMonths
    ): float {
        if ($annualRatePercent == 0) {
            return round($principal / $tenureMonths, 2);
        }

        $monthlyRate = ($annualRatePercent / 100) / 12;
        $factor      = pow(1 + $monthlyRate, $tenureMonths);
        $emi         = $principal * $monthlyRate * $factor / ($factor - 1);

        return round($emi, 2);
    }

    /**
     * Generate the full EMI schedule for a loan.
     * Saves directly to the database.
     */
    public function generateSchedule(Loan $loan): Collection
    {
        $schedules       = collect();
        $principal       = (float) $loan->disbursed_amount;
        $annualRate      = (float) $loan->interest_rate;
        $monthlyRate     = ($annualRate / 100) / 12;
        $totalTenure     = (int) $loan->tenure_months;
        $moratorium      = (int) $loan->moratorium_months;
        $gracePeriod     = (int) $loan->grace_period_months;
        $method          = $loan->interest_method;
        $firstEmiDate    = Carbon::parse($loan->first_emi_date);
        $emiAmount       = (float) $loan->emi_amount;
        $balance         = $principal;

        // Delete any existing schedule (e.g., re-generate after restructure)
        LoanEmiSchedule::where('loan_id', $loan->id)->delete();

        DB::transaction(function () use (
            &$schedules, $loan, $principal, $annualRate, $monthlyRate,
            $totalTenure, $moratorium, $gracePeriod, $method,
            $firstEmiDate, $emiAmount, &$balance
        ) {
            $emiNumber = 1;

            for ($month = 1; $month <= $totalTenure; $month++) {
                $dueDate = $firstEmiDate->copy()->addMonths($month - 1);

                // Grace period — no EMI, no interest
                if ($month <= $gracePeriod) {
                    $schedule = LoanEmiSchedule::create([
                        'loan_id'          => $loan->id,
                        'emi_number'       => $emiNumber++,
                        'due_date'         => $dueDate,
                        'emi_amount'       => 0,
                        'principal_amount' => 0,
                        'interest_amount'  => 0,
                        'opening_balance'  => $balance,
                        'closing_balance'  => $balance,
                        'status'           => 'pending',
                        'is_grace'         => true,
                    ]);
                    $schedules->push($schedule);
                    continue;
                }

                // Moratorium period — interest only, no principal
                if ($month <= ($gracePeriod + $moratorium)) {
                    $interest = match ($method) {
                        'reducing_daily' => $this->dailyInterest($balance, $annualRate, $dueDate),
                        default          => round($balance * $monthlyRate, 2),
                    };
                    $schedule = LoanEmiSchedule::create([
                        'loan_id'          => $loan->id,
                        'emi_number'       => $emiNumber++,
                        'due_date'         => $dueDate,
                        'emi_amount'       => $interest,
                        'principal_amount' => 0,
                        'interest_amount'  => $interest,
                        'opening_balance'  => $balance,
                        'closing_balance'  => $balance,
                        'status'           => 'pending',
                        'is_moratorium'    => true,
                    ]);
                    $schedules->push($schedule);
                    continue;
                }

                // Normal EMI period
                $interestForPeriod = match ($method) {
                    'flat'           => round($principal * ($annualRate / 100) / 12, 2),
                    'reducing_daily' => $this->dailyInterest($balance, $annualRate, $dueDate),
                    default          => round($balance * $monthlyRate, 2),
                };

                // Last EMI — pay off exact remaining balance
                $remainingEmiMonths = $totalTenure - $month + 1;
                if ($remainingEmiMonths === 1) {
                    $principalForPeriod = round($balance, 2);
                    $emiForPeriod       = round($principalForPeriod + $interestForPeriod, 2);
                } else {
                    $emiForPeriod       = $emiAmount;
                    $principalForPeriod = round($emiForPeriod - $interestForPeriod, 2);
                    // Guard: ensure principal not negative (rounding issues)
                    if ($principalForPeriod < 0) {
                        $principalForPeriod = 0;
                    }
                    if ($principalForPeriod > $balance) {
                        $principalForPeriod = round($balance, 2);
                        $emiForPeriod       = round($principalForPeriod + $interestForPeriod, 2);
                    }
                }

                $closingBalance = round($balance - $principalForPeriod, 2);

                $schedule = LoanEmiSchedule::create([
                    'loan_id'          => $loan->id,
                    'emi_number'       => $emiNumber++,
                    'due_date'         => $dueDate,
                    'emi_amount'       => $emiForPeriod,
                    'principal_amount' => $principalForPeriod,
                    'interest_amount'  => $interestForPeriod,
                    'opening_balance'  => round($balance, 2),
                    'closing_balance'  => max(0, $closingBalance),
                    'status'           => 'pending',
                ]);

                $schedules->push($schedule);
                $balance = max(0, $closingBalance);
            }
        });

        return $schedules;
    }

    /**
     * Simulate the schedule WITHOUT saving to DB.
     * Used for pre-approval EMI simulation.
     */
    public function simulateSchedule(
        float  $principal,
        float  $annualRatePercent,
        int    $tenureMonths,
        string $method = 'reducing',
        ?string $firstEmiDate = null,
        int    $moratoriumMonths = 0,
        int    $gracePeriodMonths = 0
    ): array {
        $firstDate   = $firstEmiDate ? Carbon::parse($firstEmiDate) : Carbon::today()->addMonth();
        $monthlyRate = ($annualRatePercent / 100) / 12;
        $emiAmount   = $this->calculateEmi($principal, $annualRatePercent, $tenureMonths, $method, $moratoriumMonths);
        $balance     = $principal;
        $rows        = [];
        $totalInterest = 0;

        for ($month = 1; $month <= $tenureMonths; $month++) {
            $dueDate = $firstDate->copy()->addMonths($month - 1);

            if ($month <= $gracePeriodMonths) {
                $rows[] = [
                    'emi_number'       => $month,
                    'due_date'         => $dueDate->format('d/m/Y'),
                    'emi_amount'       => 0,
                    'principal'        => 0,
                    'interest'         => 0,
                    'balance'          => round($balance, 2),
                    'type'             => 'Grace',
                ];
                continue;
            }

            if ($month <= ($gracePeriodMonths + $moratoriumMonths)) {
                $interest = round($balance * $monthlyRate, 2);
                $totalInterest += $interest;
                $rows[] = [
                    'emi_number'  => $month,
                    'due_date'    => $dueDate->format('d/m/Y'),
                    'emi_amount'  => $interest,
                    'principal'   => 0,
                    'interest'    => $interest,
                    'balance'     => round($balance, 2),
                    'type'        => 'Moratorium',
                ];
                continue;
            }

            $interest   = match ($method) {
                'flat'  => round($principal * ($annualRatePercent / 100) / 12, 2),
                default => round($balance * $monthlyRate, 2),
            };

            $remaining = $tenureMonths - $month + 1;
            if ($remaining === 1) {
                $principalPart = round($balance, 2);
                $emiRow        = round($principalPart + $interest, 2);
            } else {
                $emiRow        = $emiAmount;
                $principalPart = round($emiRow - $interest, 2);
                if ($principalPart > $balance) { $principalPart = round($balance, 2); }
            }

            $balance = round(max(0, $balance - $principalPart), 2);
            $totalInterest += $interest;

            $rows[] = [
                'emi_number'  => $month,
                'due_date'    => $dueDate->format('d/m/Y'),
                'emi_amount'  => $emiRow,
                'principal'   => $principalPart,
                'interest'    => $interest,
                'balance'     => $balance,
                'type'        => 'EMI',
            ];
        }

        return [
            'emi_amount'       => $emiAmount,
            'total_interest'   => round($totalInterest, 2),
            'total_payable'    => round($principal + $totalInterest, 2),
            'schedule'         => $rows,
        ];
    }

    /**
     * Recalculate schedule after partial prepayment.
     * Option: reduce_tenure (keep EMI same) or reduce_emi (keep tenure same).
     */
    public function recalculateAfterPrepayment(
        Loan  $loan,
        float $prepaidPrincipal,
        string $option = 'reduce_tenure'
    ): array {
        $newPrincipal = (float)$loan->outstanding_principal - $prepaidPrincipal;
        if ($newPrincipal < 0) {
            $newPrincipal = 0;
        }

        $remainingEmis = $loan->emiSchedules()->where('status', 'pending')->count();

        if ($option === 'reduce_tenure') {
            // Same EMI, find new tenure
            $newTenure = $this->findTenureForEmi(
                $newPrincipal,
                (float)$loan->interest_rate,
                (float)$loan->emi_amount,
                $loan->interest_method
            );
            return [
                'new_principal'   => $newPrincipal,
                'new_tenure'      => $newTenure,
                'new_emi'         => (float)$loan->emi_amount,
                'emis_reduced_by' => $remainingEmis - $newTenure,
            ];
        } else {
            // Reduce EMI, keep tenure
            $newEmi = $this->calculateEmi(
                $newPrincipal,
                (float)$loan->interest_rate,
                $remainingEmis,
                $loan->interest_method
            );
            return [
                'new_principal'  => $newPrincipal,
                'new_tenure'     => $remainingEmis,
                'new_emi'        => $newEmi,
                'emi_reduced_by' => (float)$loan->emi_amount - $newEmi,
            ];
        }
    }

    /**
     * Find the number of EMIs needed to clear a principal at given EMI amount.
     */
    protected function findTenureForEmi(
        float  $principal,
        float  $annualRatePercent,
        float  $emiAmount,
        string $method = 'reducing'
    ): int {
        if ($emiAmount <= 0 || $principal <= 0) {
            return 0;
        }

        if ($method === 'flat') {
            // Approximate for flat
            return (int) ceil($principal / $emiAmount);
        }

        $monthlyRate = ($annualRatePercent / 100) / 12;
        if ($monthlyRate == 0) {
            return (int) ceil($principal / $emiAmount);
        }

        // n = -log(1 - (P * r / EMI)) / log(1 + r)
        $numerator = log(1 - ($principal * $monthlyRate / $emiAmount));
        $tenure    = (int) ceil(-$numerator / log(1 + $monthlyRate));

        return max(1, $tenure);
    }

    /**
     * Calculate daily interest for a given month (for reducing_daily method).
     */
    protected function dailyInterest(float $balance, float $annualRatePercent, Carbon $forMonth): float
    {
        $daysInMonth = $forMonth->daysInMonth;
        $dailyRate   = ($annualRatePercent / 100) / 365;
        return round($balance * $dailyRate * $daysInMonth, 2);
    }

    /**
     * Calculate outstanding penalty for a loan based on overdue EMIs.
     */
    public function calculatePenalty(Loan $loan, ?float $penaltyRatePerDay = null): float
    {
        $ratePerDay = $penaltyRatePerDay ?? config('lms.penalty.rate_per_day_percent', 0.05);
        $graceDays  = config('lms.penalty.grace_days', 3);
        $today      = Carbon::today();
        $totalPenalty = 0;

        $overdueEmis = $loan->emiSchedules()
            ->whereIn('status', ['pending', 'partial'])
            ->where('due_date', '<', $today)
            ->get();

        foreach ($overdueEmis as $emi) {
            $dueDate     = Carbon::parse($emi->due_date);
            $overdueDays = $dueDate->diffInDays($today);

            if ($overdueDays <= $graceDays) {
                continue;
            }

            $overdueAmount  = (float)$emi->emi_amount - (float)$emi->paid_amount;
            $penalty        = $overdueAmount * ($ratePerDay / 100) * $overdueDays;
            $totalPenalty  += round($penalty, 2);
        }

        return round($totalPenalty, 2);
    }

    /**
     * Process EMI payment for a loan and allocate across pending schedules.
     */
    public function processPayment(Loan $loan, float $amount, array $details = []): \App\Models\LoanPayment
    {
        return DB::transaction(function () use ($loan, $amount, $details) {
            $paymentDate = $details['payment_date'] ?? date('Y-m-d');
            $mode        = $details['payment_mode'] ?? 'cash';
            $collectedBy = $details['collected_by'] ?? \Illuminate\Support\Facades\Auth::id();
            $branchId    = $details['branch_id'] ?? $loan->branch_id;
            $receiptNo   = 'RCPT-' . time() . rand(100, 999);

            $remaining     = $amount;
            $principalPaid = 0;
            $interestPaid  = 0;

            $schedules = $loan->emiSchedules()
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->orderBy('due_date')
                ->get();

            foreach ($schedules as $schedule) {
                if ($remaining <= 0) {
                    break;
                }

                $due = (float)$schedule->emi_amount - (float)$schedule->paid_amount;
                if ($remaining >= $due) {
                    $remaining -= $due;
                    $schedule->update([
                        'paid_amount' => $schedule->emi_amount,
                        'status'      => 'paid',
                        'paid_at'     => $paymentDate,
                    ]);
                    $principalPaid += (float)$schedule->principal_amount;
                    $interestPaid  += (float)$schedule->interest_amount;
                } else {
                    $schedule->update([
                        'paid_amount' => (float)$schedule->paid_amount + $remaining,
                        'status'      => 'partial',
                    ]);
                    $principalPaid += $remaining;
                    $remaining      = 0;
                }
            }

            $payment = \App\Models\LoanPayment::create([
                'company_id'     => $loan->company_id,
                'branch_id'      => $branchId,
                'loan_id'        => $loan->id,
                'collected_by'   => $collectedBy,
                'receipt_no'     => $receiptNo,
                'payment_date'   => $paymentDate,
                'amount'         => $amount,
                'principal_paid' => $principalPaid,
                'interest_paid'  => $interestPaid,
                'penalty_paid'   => 0,
                'payment_mode'   => $mode,
                'reference_no'   => $details['reference_no'] ?? null,
                'bank_name'      => $details['bank_name'] ?? null,
                'cheque_date'    => $details['cheque_date'] ?? null,
                'remarks'        => $details['remarks'] ?? null,
                'status'         => 'completed',
            ]);

            $newOutstanding = max(0, (float)$loan->outstanding_principal - $principalPaid);
            $loan->update(['outstanding_principal' => $newOutstanding]);

            if ($newOutstanding <= 0) {
                $loan->update(['status' => 'closed', 'closed_at' => now()]);
            }

            return $payment;
        });
    }

    /**
     * Calculate foreclosure amount for a loan as of today.
     */
    public function calculateForeclosure(Loan $loan, float $foreclosureChargePercent = 2.0): array
    {
        $outstanding        = (float)$loan->outstanding_principal;
        $outstandingInterest = (float)$loan->outstanding_interest;
        $outstandingPenalty = (float)$loan->outstanding_penalty;
        $foreclosureCharge  = round($outstanding * $foreclosureChargePercent / 100, 2);

        return [
            'outstanding_principal'  => $outstanding,
            'outstanding_interest'   => $outstandingInterest,
            'accrued_interest'       => $outstandingInterest,
            'outstanding_penalty'    => $outstandingPenalty,
            'foreclosure_charges'    => $foreclosureCharge,
            'foreclosure_charge'     => $foreclosureCharge,
            'total_foreclosure_amount' => $outstanding + $outstandingInterest + $outstandingPenalty + $foreclosureCharge,
        ];
    }
}
