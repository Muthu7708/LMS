@extends('layouts.app')
@section('title', 'NPA Loans')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active">NPA Loans</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-shield-x me-2 text-danger"></i>NPA (Non-Performing Assets) Report</h1>
        <p class="page-subtitle">Loans classified as NPA (over 90 days overdue)</p>
    </div>
</div>

<div class="lms-card fade-in-up">
    <div class="lms-table-wrapper" style="border:none">
        <table class="lms-table">
            <thead>
                <tr><th>Loan No.</th><th>Borrower</th><th>Branch</th><th>Disbursed</th><th>Outstanding Principal</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($npaLoans as $loan)
                <tr>
                    <td class="fw-bold text-primary">{{ $loan->loan_no }}</td>
                    <td>{{ $loan->customer->full_name ?? '—' }}</td>
                    <td>{{ $loan->branch->name ?? '—' }}</td>
                    <td>{{ config('lms.currency_symbol') }}{{ number_format($loan->disbursed_amount, 2) }}</td>
                    <td class="fw-bold text-danger">{{ config('lms.currency_symbol') }}{{ number_format($loan->outstanding_principal, 2) }}</td>
                    <td><span class="badge-status badge-danger">NPA</span></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No NPA accounts! Healthy portfolio.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
