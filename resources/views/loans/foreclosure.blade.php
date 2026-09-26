@extends('layouts.app')
@section('title', 'Foreclose Loan — ' . $loan->loan_no)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('loans.index') }}">Loans</a></li>
<li class="breadcrumb-item"><a href="{{ route('loans.show', $loan) }}">{{ $loan->loan_no }}</a></li>
<li class="breadcrumb-item active">Foreclosure</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title text-warning"><i class="bi bi-door-closed me-2"></i>Loan Foreclosure Settlement</h1>
        <p class="page-subtitle">Early settlement and closure calculation for Loan <strong>{{ $loan->loan_no }}</strong></p>
    </div>
    <a href="{{ route('loans.show', $loan) }}" class="btn-lms-secondary"><i class="bi bi-arrow-left"></i>Cancel</a>
</div>

<div class="row g-4 fade-in-up">
    <div class="col-lg-7">
        <div class="lms-card mb-4">
            <div class="lms-card-header">
                <h5 class="lms-card-title"><i class="bi bi-calculator text-warning"></i>Settlement Breakdown</h5>
            </div>
            <div class="lms-card-body">
                <table class="table table-borderless table-sm text-secondary" style="font-size:14px">
                    <tr><td class="text-muted">Outstanding Principal:</td><td class="text-end fw-semibold text-white">{{ config('lms.currency_symbol') }}{{ number_format($calculation['outstanding_principal'] ?? 0, 2) }}</td></tr>
                    <tr><td class="text-muted">Accrued Interest till Date:</td><td class="text-end fw-semibold text-white">{{ config('lms.currency_symbol') }}{{ number_format($calculation['accrued_interest'] ?? $calculation['outstanding_interest'] ?? 0, 2) }}</td></tr>
                    <tr><td class="text-muted">Outstanding Penalties:</td><td class="text-end fw-semibold text-danger">{{ config('lms.currency_symbol') }}{{ number_format($calculation['outstanding_penalty'] ?? 0, 2) }}</td></tr>
                    <tr><td class="text-muted">Foreclosure Charges ({{ config('lms.foreclosure_charge_percent', 2) }}%):</td><td class="text-end fw-semibold text-white">{{ config('lms.currency_symbol') }}{{ number_format($calculation['foreclosure_charges'] ?? $calculation['foreclosure_charge'] ?? 0, 2) }}</td></tr>
                    <tr class="border-top border-secondary"><td class="fw-bold text-white fs-5 pt-3">Total Settlement Payable:</td><td class="text-end fw-bold text-warning fs-4 pt-3">{{ config('lms.currency_symbol') }}{{ number_format($calculation['total_foreclosure_amount'] ?? 0, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <form method="POST" action="{{ route('loans.foreclosure', $loan) }}" class="lms-card">
            @csrf
            <div class="lms-card-header">
                <h5 class="lms-card-title"><i class="bi bi-credit-card text-success"></i>Collect Settlement Payment</h5>
            </div>
            <div class="lms-card-body">
                <div class="mb-3">
                    <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                    <select name="payment_mode" class="form-select" required>
                        <option value="neft">NEFT / RTGS / Online</option>
                        <option value="cheque">Cheque</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Transaction Reference / UTR</label>
                    <input type="text" name="payment_reference" class="form-control" placeholder="UTR, Cheque No...">
                </div>
                <button type="submit" class="btn-lms-primary w-100 justify-content-center py-3 fs-6" onclick="return confirm('Process foreclosure payment and close loan?')">
                    <i class="bi bi-check-circle-fill me-2"></i>Confirm Foreclosure & Close Loan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
