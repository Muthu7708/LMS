<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\ReceiptReprint;
use App\Services\EmiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected EmiService $emiService;

    public function __construct(EmiService $emiService)
    {
        $this->emiService = $emiService;
    }

    public function create(Loan $loan)
    {
        if (!in_array($loan->status, ['active', 'overdue', 'npa', 'restructured'])) {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Payments can only be collected for active, overdue, restructured, or NPA loans.');
        }

        $overdueEmis = $loan->emiSchedules()->whereIn('status', ['pending', 'partial', 'overdue'])->orderBy('due_date')->get();
        return view('payments.create', compact('loan', 'overdueEmis'));
    }

    public function store(Request $request, Loan $loan)
    {
        if (!in_array($loan->status, ['active', 'overdue', 'npa', 'restructured'])) {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Payments can only be collected for active, overdue, restructured, or NPA loans.');
        }

        $data = $request->validate([
            'amount'       => 'required|numeric|min:1',
            'payment_date' => 'required|date|before_or_equal:today',
            'payment_mode' => 'required|string',
            'reference_no' => 'nullable|string',
            'bank_name'    => 'nullable|string',
            'cheque_date'  => 'nullable|date',
            'remarks'      => 'nullable|string',
        ]);

        $payment = $this->emiService->processPayment($loan, $data['amount'], [
            ...$data,
            'collected_by' => Auth::id(),
            'branch_id'    => $loan->branch_id,
        ]);

        return redirect()->route('loans.payments.receipt', $payment)
            ->with('success', "Payment received! Receipt #{$payment->receipt_no} generated.");
    }

    public function receipt(LoanPayment $payment)
    {
        $payment->load(['loan.customer', 'loan.branch', 'collectedBy', 'reprints']);
        return view('payments.receipt', compact('payment'));
    }

    public function reprint(Request $request, LoanPayment $payment)
    {
        $request->validate(['reason' => 'required|string']);

        ReceiptReprint::create([
            'payment_id'  => $payment->id,
            'reprinted_by'=> Auth::id(),
            'receipt_no'  => $payment->receipt_no,
            'reason'      => $request->reason,
            'ip_address'  => $request->ip(),
            'reprinted_at'=> now(),
        ]);

        return redirect()->route('loans.payments.receipt', $payment)->with('success', 'Receipt reprint logged.');
    }

    public function reverse(Request $request, LoanPayment $payment)
    {
        $request->validate(['reversal_reason' => 'required|string|min:5']);

        if ($payment->is_reversed) {
            return back()->with('error', 'Payment is already reversed.');
        }

        DB::transaction(function () use ($request, $payment) {
            $payment->update([
                'is_reversed'    => true,
                'reversed_at'    => now(),
                'reversed_by'    => Auth::id(),
                'reversal_reason'=> $request->reversal_reason,
            ]);

            // Adjust outstanding balance
            $loan = $payment->loan;
            $loan->increment('outstanding_principal', $payment->principal_paid);
            if ($loan->status === 'closed') {
                $loan->update(['status' => 'active', 'closed_at' => null]);
            }
        });

        return back()->with('success', "Payment #{$payment->receipt_no} reversed.");
    }
}
