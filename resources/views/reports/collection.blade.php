@extends('layouts.app')
@section('title', 'Collection Report')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active">Collection</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-wallet2 me-2 text-primary"></i>Collection Report</h1>
        <p class="page-subtitle">Total Collected Amount: <strong class="text-success">{{ config('lms.currency_symbol') }}{{ number_format($totalAmount, 2) }}</strong></p>
    </div>
</div>

<div class="lms-card fade-in-up">
    <div class="lms-card-header">
        <form method="GET" class="d-flex gap-2 flex-wrap" style="flex:1">
            <input type="date" name="start_date" class="form-control" style="width:160px" value="{{ request('start_date') }}">
            <input type="date" name="end_date" class="form-control" style="width:160px" value="{{ request('end_date') }}">
            <select name="payment_mode" class="form-select" style="width:150px">
                <option value="">All Modes</option>
                <option value="cash">Cash</option>
                <option value="upi">UPI</option>
                <option value="neft">NEFT</option>
                <option value="cheque">Cheque</option>
            </select>
            <button type="submit" class="btn-lms-primary"><i class="bi bi-funnel"></i>Filter</button>
        </form>
    </div>
    <div class="lms-table-wrapper" style="border:none">
        <table class="lms-table">
            <thead>
                <tr><th>Receipt No.</th><th>Borrower</th><th>Loan No.</th><th>Payment Date</th><th>Amount Paid</th><th>Mode</th><th>Collected By</th></tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                <tr>
                    <td class="fw-bold text-primary">{{ $p->receipt_no }}</td>
                    <td>{{ $p->loan->customer->full_name ?? '—' }}</td>
                    <td>{{ $p->loan->loan_no }}</td>
                    <td>{{ $p->payment_date->format('d M Y') }}</td>
                    <td class="fw-bold text-success">{{ config('lms.currency_symbol') }}{{ number_format($p->amount, 2) }}</td>
                    <td class="text-uppercase">{{ $p->payment_mode }}</td>
                    <td>{{ $p->collectedBy->name ?? 'System' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No collection records found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center p-3">
        <span style="font-size:13px;color:var(--text-muted)">Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ $payments->total() }}</span>
        {{ $payments->withQueryString()->links('partials.pagination') }}
    </div>
</div>
@endsection
