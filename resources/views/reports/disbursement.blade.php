@extends('layouts.app')
@section('title', 'Disbursement Report')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active">Disbursement</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-arrow-up-circle me-2 text-success"></i>Disbursement Report</h1>
        <p class="page-subtitle">Total Disbursed Amount: <strong class="text-success">{{ config('lms.currency_symbol') }}{{ number_format($totalAmount, 2) }}</strong></p>
    </div>
</div>

<div class="lms-card fade-in-up">
    <div class="lms-card-header">
        <form method="GET" class="d-flex gap-2 flex-wrap" style="flex:1">
            <input type="date" name="start_date" class="form-control" style="width:160px" value="{{ request('start_date') }}" placeholder="Start Date">
            <input type="date" name="end_date" class="form-control" style="width:160px" value="{{ request('end_date') }}" placeholder="End Date">
            <button type="submit" class="btn-lms-primary"><i class="bi bi-funnel"></i>Filter</button>
        </form>
    </div>
    <div class="lms-table-wrapper" style="border:none">
        <table class="lms-table">
            <thead>
                <tr><th>Loan No.</th><th>Borrower</th><th>Disbursed Date</th><th>Disbursed Amount</th><th>Interest Rate</th><th>Branch</th></tr>
            </thead>
            <tbody>
                @forelse($disbursements as $loan)
                <tr>
                    <td class="fw-bold text-primary">{{ $loan->loan_no }}</td>
                    <td>{{ $loan->customer->full_name ?? '—' }}</td>
                    <td>{{ $loan->disbursement_date ? $loan->disbursement_date->format('d M Y') : '—' }}</td>
                    <td class="fw-bold text-success">{{ config('lms.currency_symbol') }}{{ number_format($loan->disbursed_amount, 2) }}</td>
                    <td>{{ $loan->interest_rate }}%</td>
                    <td>{{ $loan->branch->name ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No disbursements found for selected filters</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center p-3">
        <span style="font-size:13px;color:var(--text-muted)">Showing {{ $disbursements->firstItem() }}–{{ $disbursements->lastItem() }} of {{ $disbursements->total() }}</span>
        {{ $disbursements->withQueryString()->links('partials.pagination') }}
    </div>
</div>
@endsection
