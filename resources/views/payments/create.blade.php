@extends('layouts.app')
@section('title', 'Collect Repayment — ' . $loan->loan_no)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('loans.index') }}">Loans</a></li>
<li class="breadcrumb-item"><a href="{{ route('loans.show', $loan) }}">{{ $loan->loan_no }}</a></li>
<li class="breadcrumb-item active">Collect Payment</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-cash-coin me-2 text-success"></i>Collect Repayment</h1>
        <p class="page-subtitle">Record payment for Loan <strong>{{ $loan->loan_no }}</strong> | Borrower: <strong>{{ $loan->customer->full_name }}</strong></p>
    </div>
    <a href="{{ route('loans.show', $loan) }}" class="btn-lms-secondary"><i class="bi bi-arrow-left"></i>Back to Loan</a>
</div>

<div class="row g-4 fade-in-up">
    <div class="col-lg-7">
        <form method="POST" action="{{ route('loans.payments.store', $loan) }}" class="lms-card">
            @csrf
            <div class="lms-card-header">
                <h5 class="lms-card-title"><i class="bi bi-receipt text-primary"></i>Payment Entry Form</h5>
            </div>
            <div class="lms-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Payment Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control form-control-lg fw-bold text-success @error('amount') is-invalid @enderror" value="{{ old('amount', $overdueEmis->first()?->balance_due ?? 0) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control form-control-lg @error('payment_date') is-invalid @enderror" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode" class="form-select @error('payment_mode') is-invalid @enderror" required>
                            <option value="cash">Cash</option>
                            <option value="upi">UPI / QR Code</option>
                            <option value="neft">NEFT / RTGS</option>
                            <option value="cheque">Cheque</option>
                            <option value="nach">NACH / Auto-Debit</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reference No / Transaction ID</label>
                        <input type="text" name="reference_no" class="form-control" placeholder="UTR, UPI Ref, Cheque No...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bank Name (if Cheque/NEFT)</label>
                        <input type="text" name="bank_name" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cheque Date</label>
                        <input type="date" name="cheque_date" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Cashier Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <hr class="border-secondary opacity-25 my-4">
                <button type="submit" class="btn-lms-primary w-100 justify-content-center py-3 fs-6">
                    <i class="bi bi-printer-fill me-2"></i>Process Payment & Print Receipt
                </button>
            </div>
        </form>
    </div>

    <div class="col-lg-5">
        <div class="lms-card mb-4">
            <div class="lms-card-header"><h5 class="lms-card-title"><i class="bi bi-clock-history text-warning"></i>Pending & Due Instalments</h5></div>
            <div style="max-height:380px; overflow-y:auto">
                @forelse($overdueEmis as $emi)
                <div class="p-3 border-bottom border-secondary d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-white">Instalment #{{ $emi->emi_number }}</div>
                        <div class="text-muted" style="font-size:12px">Due: {{ $emi->due_date->format('d M Y') }}</div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-warning">{{ config('lms.currency_symbol') }}{{ number_format($emi->balance_due, 2) }}</div>
                        <span class="badge bg-danger" style="font-size:10px">{{ $emi->isOverdue() ? $emi->due_date->diffInDays().' days overdue' : 'Due Soon' }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">All instalments up to date!</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
