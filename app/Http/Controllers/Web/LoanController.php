<?php

namespace App\Http\Controllers\Web;

use App\Enums\LoanStatus;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\LoanApproval;
use App\Models\LoanCollateral;
use App\Models\LoanDisbursement;
use App\Models\LoanForeclosure;
use App\Models\LoanGuarantor;
use App\Models\LoanVerification;
use App\Models\LoanWaiver;
use App\Services\EmiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    protected EmiService $emiService;

    public function __construct(EmiService $emiService)
    {
        $this->emiService = $emiService;
    }

    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = Loan::with(['customer', 'branch'])
            ->when(! $user->hasRole(['super_admin', 'company_admin']), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->when($request->search, function ($q, $search) {
                $q->where('loan_no', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', fn ($c) => $c->where('first_name', 'LIKE', "%{$search}%")->orWhere('last_name', 'LIKE', "%{$search}%")->orWhere('mobile', 'LIKE', "%{$search}%"));
            })
            ->when($request->status, fn ($q, $st) => $q->where('status', $st))
            ->when($request->loan_type, fn ($q, $lt) => $q->where('loan_type', $lt))
            ->when($request->branch_id, fn ($q, $b) => $q->where('branch_id', $b))
            ->latest();

        $loans = $query->paginate(20)->withQueryString();
        $branches = Branch::active()->get();

        return view('loans.index', compact('loans', 'branches'));
    }

    public function create(Request $request)
    {
        $customers = Customer::where('status', 'active')
            ->when(! Auth::user()->hasRole(['super_admin', 'company_admin']), fn ($q) => $q->where('branch_id', Auth::user()->branch_id))
            ->get();
        $branches = Branch::active()->get();
        $selectedCustomerId = $request->customer_id;

        return view('loans.create', compact('customers', 'branches', 'selectedCustomerId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'             => 'required|exists:customers,id',
            'branch_id'               => 'required|exists:branches,id',
            'loan_type'               => 'required|string',
            'applied_amount'          => 'required|numeric|min:1000',
            'interest_rate'           => 'required|numeric|min:0|max:100',
            'tenure_months'           => 'required|integer|min:1|max:360',
            'repayment_frequency'     => 'required|string',
            'interest_type'           => 'required|in:flat,reducing',
            'grace_period_days'       => 'nullable|integer|min:0',
            'moratorium_period_months'=> 'nullable|integer|min:0',
            'processing_fee_rate'     => 'nullable|numeric|min:0',
            'first_emi_date'          => 'required|date|after_or_equal:today',
            'purpose'                 => 'nullable|string',
            'remarks'                 => 'nullable|string',
        ]);

        $loan = DB::transaction(function () use ($data) {
            $processingFeeAmount = isset($data['processing_fee_rate'])
                ? round(($data['applied_amount'] * $data['processing_fee_rate']) / 100, 2)
                : 0;

            $emiAmount = $this->emiService->calculateEmi(
                (float) $data['applied_amount'],
                (float) $data['interest_rate'],
                (int) $data['tenure_months'],
                $data['interest_type'],
                (int) ($data['moratorium_period_months'] ?? 0)
            );

            $loan = Loan::create([
                'company_id'             => Auth::user()->company_id ?? 1,
                'branch_id'              => $data['branch_id'],
                'customer_id'            => $data['customer_id'],
                'loan_officer_id'        => Auth::id(),
                'loan_no'                => $this->generateLoanNo(),
                'loan_type'              => $data['loan_type'],
                'purpose'                => $data['purpose'] ?? null,
                'applied_amount'         => $data['applied_amount'],
                'approved_amount'        => $data['applied_amount'],
                'tenure_months'          => $data['tenure_months'],
                'interest_rate'          => $data['interest_rate'],
                'interest_method'        => $data['interest_type'],
                'payment_frequency'      => $data['repayment_frequency'] ?? 'monthly',
                'processing_fee'         => $processingFeeAmount,
                'grace_period_months'    => $data['grace_period_days'] ?? 0,
                'moratorium_months'      => $data['moratorium_period_months'] ?? 0,
                'application_date'       => now()->toDateString(),
                'first_emi_date'         => $data['first_emi_date'],
                'emi_amount'             => $emiAmount,
                'status'                 => 'draft',
                'internal_notes'         => $data['remarks'] ?? null,
            ]);

            return $loan;
        });

        return redirect()->route('loans.show', $loan)
            ->with('success', "Loan Application {$loan->loan_no} created as Draft.");
    }

    public function edit(Loan $loan)
    {
        if (in_array($loan->status, ['disbursed', 'active', 'closed', 'rejected'])) {
            return back()->with('error', 'Disbursed or closed loans cannot be edited.');
        }

        $customers = Customer::where('status', 'active')->get();
        $branches = Branch::active()->get();

        return view('loans.edit', compact('loan', 'customers', 'branches'));
    }

    public function update(Request $request, Loan $loan)
    {
        if (in_array($loan->status, ['disbursed', 'active', 'closed', 'rejected'])) {
            return back()->with('error', 'Disbursed or closed loans cannot be edited.');
        }

        $data = $request->validate([
            'customer_id'             => 'required|exists:customers,id',
            'branch_id'               => 'required|exists:branches,id',
            'loan_type'               => 'required|string',
            'applied_amount'          => 'required|numeric|min:1000',
            'interest_rate'           => 'required|numeric|min:0|max:100',
            'tenure_months'           => 'required|integer|min:1|max:360',
            'repayment_frequency'     => 'required|string',
            'interest_type'           => 'required|in:flat,reducing',
            'grace_period_days'       => 'nullable|integer|min:0',
            'moratorium_period_months'=> 'nullable|integer|min:0',
            'processing_fee_rate'     => 'nullable|numeric|min:0',
            'first_emi_date'          => 'required|date',
            'purpose'                 => 'nullable|string',
            'remarks'                 => 'nullable|string',
        ]);

        $processingFeeAmount = isset($data['processing_fee_rate'])
            ? round(($data['applied_amount'] * $data['processing_fee_rate']) / 100, 2)
            : 0;

        $emiAmount = $this->emiService->calculateEmi(
            (float) $data['applied_amount'],
            (float) $data['interest_rate'],
            (int) $data['tenure_months'],
            $data['interest_type'],
            (int) ($data['moratorium_period_months'] ?? 0)
        );

        $loan->update([
            'branch_id'              => $data['branch_id'],
            'customer_id'            => $data['customer_id'],
            'loan_type'              => $data['loan_type'],
            'purpose'                => $data['purpose'] ?? null,
            'applied_amount'         => $data['applied_amount'],
            'approved_amount'        => $data['applied_amount'],
            'tenure_months'          => $data['tenure_months'],
            'interest_rate'          => $data['interest_rate'],
            'interest_method'        => $data['interest_type'],
            'payment_frequency'      => $data['repayment_frequency'] ?? 'monthly',
            'processing_fee'         => $processingFeeAmount,
            'grace_period_months'    => $data['grace_period_days'] ?? 0,
            'moratorium_months'      => $data['moratorium_period_months'] ?? 0,
            'first_emi_date'         => $data['first_emi_date'],
            'emi_amount'             => $emiAmount,
            'internal_notes'         => $data['remarks'] ?? null,
        ]);

        return redirect()->route('loans.show', $loan)
            ->with('success', "Loan Application {$loan->loan_no} updated successfully.");
    }

    public function show(Loan $loan)
    {
        $loan->load([
            'customer', 'branch', 'emiSchedules', 'payments.collectedBy',
            'collaterals', 'guarantors', 'verifications.verifiedBy',
            'approvals.approvedBy', 'disbursements.disbursedBy',
            'waivers.requestedBy', 'documents.currentVersion',
        ]);

        return view('loans.show', compact('loan'));
    }

    public function submit(Loan $loan)
    {
        if ($loan->status !== 'draft') {
            return back()->with('error', 'Only draft loans can be submitted.');
        }

        $loan->update(['status' => 'submitted']);
        return back()->with('success', "Loan {$loan->loan_no} submitted for review.");
    }

    public function verify(Request $request, Loan $loan)
    {
        $request->validate([
            'verification_type' => 'required|string',
            'status'            => 'required|in:passed,failed,requires_further_review',
            'remarks'           => 'required|string',
        ]);

        $dbStatus = match ($request->status) {
            'passed' => 'completed',
            'failed' => 'failed',
            default  => 'in_progress',
        };

        $dbType = match ($request->verification_type) {
            'field_visit' => 'field',
            'document_verification', 'credit_check' => 'document',
            'telephonic' => 'telephonic',
            'residence' => 'residence',
            'office' => 'office',
            default => in_array($request->verification_type, ['field', 'telephonic', 'document', 'residence', 'office']) ? $request->verification_type : 'document',
        };

        LoanVerification::create([
            'loan_id'          => $loan->id,
            'verified_by'      => Auth::id(),
            'verification_type'=> $dbType,
            'status'           => $dbStatus,
            'remarks'          => $request->remarks,
            'visited_at'       => now(),
        ]);

        if ($request->status === 'passed') {
            $loan->update(['status' => 'verified']);
            $msg = "Loan {$loan->loan_no} verification passed.";
        } else {
            $loan->update(['status' => 'under_review']);
            $msg = "Loan {$loan->loan_no} verification recorded.";
        }

        return back()->with('success', $msg);
    }

    public function approve(Request $request, Loan $loan)
    {
        $request->validate([
            'approved_amount' => 'required|numeric|min:1000',
            'approved_rate'   => 'required|numeric|min:0|max:100',
            'approved_tenure' => 'required|integer|min:1',
            'conditions'      => 'nullable|string',
            'remarks'         => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $loan) {
            LoanApproval::create([
                'loan_id'         => $loan->id,
                'approved_by'     => Auth::id(),
                'approval_level'  => 1,
                'approver_role'   => Auth::user()->getRoleNames()->first() ?? 'Admin',
                'action'          => 'approved',
                'approved_amount' => $request->approved_amount,
                'approved_rate'   => $request->approved_rate,
                'approved_tenure' => $request->approved_tenure,
                'conditions'      => $request->conditions,
                'remarks'         => $request->remarks,
                'actioned_at'     => now(),
            ]);

            $loan->update([
                'approved_amount' => $request->approved_amount,
                'approved_rate'   => $request->approved_rate,
                'approved_tenure' => $request->approved_tenure,
                'status'          => 'approved',
                'approved_at'     => now(),
                'approved_by'     => Auth::id(),
            ]);
        });

        return back()->with('success', "Loan {$loan->loan_no} approved.");
    }

    public function reject(Request $request, Loan $loan)
    {
        $request->validate(['reason' => 'required|string']);

        LoanApproval::create([
            'loan_id'      => $loan->id,
            'approved_by'  => Auth::id(),
            'approval_level'=> 1,
            'approver_role'=> Auth::user()->getRoleNames()->first() ?? 'Admin',
            'action'       => 'rejected',
            'remarks'      => $request->reason,
            'actioned_at'  => now(),
        ]);

        $loan->update(['status' => 'rejected']);

        return back()->with('success', "Loan {$loan->loan_no} rejected.");
    }

    public function disburse(Request $request, Loan $loan)
    {
        $request->validate([
            'amount'            => 'required|numeric|min:1',
            'mode'              => 'required|string',
            'reference_no'      => 'nullable|string',
            'bank_name'         => 'nullable|string',
            'account_number'    => 'nullable|string',
            'disbursement_date' => 'required|date',
        ]);

        DB::transaction(function () use ($request, $loan) {
            LoanDisbursement::create([
                'loan_id'          => $loan->id,
                'disbursed_by'     => Auth::id(),
                'disbursement_no'  => 'DISB-' . time(),
                'tranche_number'   => 1,
                'amount'           => $request->amount,
                'mode'             => $request->mode,
                'reference_no'     => $request->reference_no,
                'bank_name'        => $request->bank_name,
                'account_number'   => $request->account_number,
                'disbursement_date'=> $request->disbursement_date,
            ]);

            $emiAmount = $this->emiService->calculateEmi(
                (float) $request->amount,
                (float) $loan->interest_rate,
                (int) $loan->tenure_months,
                $loan->interest_method,
                (int) $loan->moratorium_months
            );

            $loan->update([
                'disbursed_amount'     => $request->amount,
                'disbursement_date'    => $request->disbursement_date,
                'outstanding_principal'=> $request->amount,
                'emi_amount'           => $emiAmount,
                'status'               => 'disbursed',
            ]);

            // Generate EMI Schedule
            $this->emiService->generateSchedule($loan);

            $loan->update(['status' => 'active']);
        });

        return back()->with('success', "Loan {$loan->loan_no} disbursed and EMI schedule generated!");
    }

    public function storeCollateral(Request $request, Loan $loan)
    {
        $data = $request->validate([
            'collateral_type' => 'required|string',
            'description'     => 'required|string',
            'estimated_value' => 'required|numeric|min:0',
            'market_value'    => 'nullable|numeric|min:0',
            'owner_name'      => 'nullable|string',
        ]);

        $loan->collaterals()->create($data);
        return back()->with('success', 'Collateral asset added.');
    }

    public function storeGuarantor(Request $request, Loan $loan)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'relationship'=> 'required|string',
            'mobile'      => 'required|string|max:15',
            'pan'         => 'nullable|string|max:10',
            'aadhaar'     => 'nullable|string|max:12',
            'monthly_income' => 'nullable|numeric|min:0',
        ]);

        $loan->guarantors()->create($data);
        return back()->with('success', 'Guarantor added.');
    }

    public function requestWaiver(Request $request, Loan $loan)
    {
        $data = $request->validate([
            'waiver_type'     => 'required|in:penalty,interest,processing_fee',
            'requested_amount'=> 'required|numeric|min:1',
            'reason'          => 'required|string',
        ]);

        $loan->waivers()->create([
            ...$data,
            'requested_by' => Auth::id(),
            'status'       => 'pending',
        ]);

        return back()->with('success', 'Waiver request submitted.');
    }

    public function approveWaiver(Request $request, LoanWaiver $waiver)
    {
        $request->validate([
            'approved_amount' => 'required|numeric|min:0',
            'remarks'         => 'nullable|string',
        ]);

        $waiver->update([
            'approved_amount'  => $request->approved_amount,
            'approved_by'      => Auth::id(),
            'approver_remarks' => $request->remarks,
            'status'           => 'approved',
            'actioned_at'      => now(),
        ]);

        return back()->with('success', 'Waiver request approved.');
    }

    public function restructure(Request $request, Loan $loan)
    {
        if (!in_array($loan->status, ['active', 'overdue', 'npa', 'restructured'])) {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Only active, overdue, restructured, or NPA loans can be restructured.');
        }

        $data = $request->validate([
            'new_interest_rate' => 'required|numeric|min:0|max:100',
            'new_tenure_months' => 'required|integer|min:1',
            'reason'            => 'required|string',
        ]);

        DB::transaction(function () use ($loan, $data) {
            $loan->update([
                'interest_rate' => $data['new_interest_rate'],
                'tenure_months' => $data['new_tenure_months'],
                'status'        => 'restructured',
            ]);

            $emiAmount = $this->emiService->calculateEmi(
                (float) $loan->outstanding_principal,
                (float) $data['new_interest_rate'],
                (int) $data['new_tenure_months'],
                $loan->interest_method
            );

            $loan->update(['emi_amount' => $emiAmount]);
            $this->emiService->generateSchedule($loan);
        });

        return back()->with('success', "Loan {$loan->loan_no} restructured successfully.");
    }

    public function foreclosureForm(Loan $loan)
    {
        if (!in_array($loan->status, ['active', 'overdue', 'npa', 'restructured'])) {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Only active, overdue, restructured, or NPA loans can be foreclosed.');
        }

        $calculation = $this->emiService->calculateForeclosure($loan);
        return view('loans.foreclosure', compact('loan', 'calculation'));
    }

    public function foreclosure(Request $request, Loan $loan)
    {
        if (!in_array($loan->status, ['active', 'overdue', 'npa', 'restructured'])) {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Only active, overdue, restructured, or NPA loans can be foreclosed.');
        }

        $request->validate([
            'payment_mode'     => 'required|string',
            'payment_reference'=> 'nullable|string',
        ]);

        $calc = $this->emiService->calculateForeclosure($loan);

        DB::transaction(function () use ($request, $loan, $calc) {
            LoanForeclosure::create([
                'loan_id'                  => $loan->id,
                'requested_by'             => Auth::id(),
                'approved_by'              => Auth::id(),
                'request_date'             => now(),
                'foreclosure_date'         => now(),
                'outstanding_principal'    => $calc['outstanding_principal'] ?? 0,
                'outstanding_interest'     => $calc['outstanding_interest'] ?? $calc['accrued_interest'] ?? 0,
                'outstanding_penalty'      => $calc['outstanding_penalty'] ?? 0,
                'foreclosure_charges'      => $calc['foreclosure_charges'] ?? $calc['foreclosure_charge'] ?? 0,
                'total_foreclosure_amount' => $calc['total_foreclosure_amount'] ?? 0,
                'amount_paid'              => $calc['total_foreclosure_amount'] ?? 0,
                'payment_mode'             => $request->payment_mode,
                'payment_reference'        => $request->payment_reference,
                'status'                   => 'completed',
            ]);

            $loan->update([
                'outstanding_principal' => 0,
                'status'                => 'closed',
                'closed_at'             => now(),
            ]);

            $loan->emiSchedules()->whereIn('status', ['pending', 'partial', 'overdue'])
                ->update(['status' => 'paid']);
        });

        return redirect()->route('loans.show', $loan)->with('success', "Loan {$loan->loan_no} foreclosed and closed successfully.");
    }

    protected function generateLoanNo(): string
    {
        $last = Loan::orderByDesc('id')->lockForUpdate()->first();
        $num = $last ? ((int) substr($last->loan_no, -5)) + 1 : 1;

        do {
            $candidate = 'LN-' . date('Y') . '-' . str_pad($num++, 5, '0', STR_PAD_LEFT);
        } while (Loan::where('loan_no', $candidate)->exists());

        return $candidate;
    }
}
