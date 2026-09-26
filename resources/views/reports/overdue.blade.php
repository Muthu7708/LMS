@extends('layouts.app')
@section('title', 'Overdue & Aging Report')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active">Overdue</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>Overdue & Aging Report</h1>
        <p class="page-subtitle">Total Overdue Instalments Amount: <strong class="text-warning">{{ config('lms.currency_symbol') }}{{ number_format($totalOverdue, 2) }}</strong></p>
    </div>
</div>

<div class="lms-card fade-in-up">
    <div class="lms-table-wrapper" style="border:none">
        <table class="lms-table">
            <thead>
                <tr><th>Borrower</th><th>Loan No.</th><th>Instalment #</th><th>Due Date</th><th>DPD (Days Past Due)</th><th>Amount Overdue</th><th>Branch</th></tr>
            </thead>
            <tbody>
                @forelse($overdueEmis as $emi)
                <tr>
                    <td class="fw-bold text-white">{{ $emi->loan->customer->full_name ?? '—' }}</td>
                    <td class="fw-bold text-primary">{{ $emi->loan->loan_no }}</td>
                    <td>#{{ $emi->emi_number }}</td>
                    <td>{{ $emi->due_date->format('d M Y') }}</td>
                    <td><span class="badge bg-danger">{{ $emi->due_date->diffInDays() }} Days</span></td>
                    <td class="fw-bold text-warning">{{ config('lms.currency_symbol') }}{{ number_format($emi->emi_amount - $emi->paid_amount, 2) }}</td>
                    <td>{{ $emi->loan->branch->name ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No overdue instalments! All accounts up to date.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center p-3">
        <span style="font-size:13px;color:var(--text-muted)">Showing {{ $overdueEmis->firstItem() }}–{{ $overdueEmis->lastItem() }} of {{ $overdueEmis->total() }}</span>
        {{ $overdueEmis->withQueryString()->links('partials.pagination') }}
    </div>
</div>
@endsection
