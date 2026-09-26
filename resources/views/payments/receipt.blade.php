@extends('layouts.app')
@section('title', 'Payment Receipt — ' . $payment->receipt_no)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('loans.index') }}">Loans</a></li>
<li class="breadcrumb-item"><a href="{{ route('loans.show', $payment->loan) }}">{{ $payment->loan->loan_no }}</a></li>
<li class="breadcrumb-item active">Receipt</li>
@endsection

@section('content')
<div class="page-header fade-in-up d-print-none">
    <div>
        <h1 class="page-title"><i class="bi bi-receipt me-2 text-primary"></i>Payment Receipt</h1>
        <p class="page-subtitle">Receipt No: <strong class="text-primary">{{ $payment->receipt_no }}</strong> | Issued: {{ $payment->created_at->format('d M Y, h:i A') }}</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn-lms-primary"><i class="bi bi-printer"></i>Print Receipt</button>
        <a href="{{ route('loans.show', $payment->loan) }}" class="btn-lms-secondary"><i class="bi bi-arrow-left"></i>Back to Loan</a>
    </div>
</div>

<div class="card bg-white text-dark p-4 mx-auto fade-in-up" style="max-width:700px; border-radius:16px; box-shadow: 0 10px 30px rgba(0,0,0,0.3)">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-start pb-3 border-bottom">
        <div>
            <h3 class="fw-bold mb-0 text-primary">{{ config('lms.company_name', 'Finance ERP') }}</h3>
            <div class="text-muted small">{{ $payment->branch->name ?? 'Head Office' }} &bull; {{ $payment->branch->address ?? '' }}</div>
            <div class="text-muted small">Ph: {{ $payment->branch->phone ?? '—' }} | Email: {{ $payment->branch->email ?? '—' }}</div>
        </div>
        <div class="text-end">
            <span class="badge bg-success fs-6">PAYMENT RECEIPT</span>
            <div class="fw-bold mt-1 fs-6">{{ $payment->receipt_no }}</div>
            <div class="text-muted small">Date: {{ $payment->payment_date->format('d/m/Y') }}</div>
        </div>
    </div>

    {{-- Details --}}
    <div class="row my-4 g-3">
        <div class="col-6">
            <div class="text-muted small uppercase">Received From:</div>
            <div class="fw-bold fs-6">{{ $payment->loan->customer->full_name }}</div>
            <div class="text-muted small">Customer No: {{ $payment->loan->customer->customer_no }}</div>
            <div class="text-muted small">Mobile: {{ $payment->loan->customer->mobile }}</div>
        </div>
        <div class="col-6 text-end">
            <div class="text-muted small uppercase">Loan Account:</div>
            <div class="fw-bold fs-6 text-primary">{{ $payment->loan->loan_no }}</div>
            <div class="text-muted small">Loan Type: {{ ucfirst($payment->loan->loan_type) }}</div>
            <div class="text-muted small">Payment Mode: <strong class="text-uppercase">{{ $payment->payment_mode }}</strong></div>
        </div>
    </div>

    {{-- Breakdown Table --}}
    <table class="table table-bordered table-sm my-3" style="font-size:13px">
        <thead class="table-light">
            <tr>
                <th>Description</th>
                <th class="text-end">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Principal Repayment</td>
                <td class="text-end fw-semibold">{{ number_format($payment->principal_paid, 2) }}</td>
            </tr>
            <tr>
                <td>Interest Collected</td>
                <td class="text-end fw-semibold">{{ number_format($payment->interest_paid, 2) }}</td>
            </tr>
            @if($payment->penalty_paid > 0)
            <tr>
                <td>Penalty / Overdue Charges</td>
                <td class="text-end fw-semibold text-danger">{{ number_format($payment->penalty_paid, 2) }}</td>
            </tr>
            @endif
            @if($payment->excess_amount > 0)
            <tr>
                <td>Excess / Advance Payment</td>
                <td class="text-end fw-semibold text-info">{{ number_format($payment->excess_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="table-dark text-white fw-bold fs-6">
                <td>TOTAL AMOUNT RECEIVED</td>
                <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="d-flex justify-content-between align-items-end mt-5 pt-3">
        <div class="text-muted small">
            <div>Collected By: <strong>{{ $payment->collectedBy->name ?? 'System' }}</strong></div>
            <div>This is a computer-generated receipt.</div>
        </div>
        <div class="text-center" style="border-top:1px solid #ccc; width:180px; padding-top:4px">
            <span class="text-muted small">Authorized Signatory</span>
        </div>
    </div>
</div>
@endsection
