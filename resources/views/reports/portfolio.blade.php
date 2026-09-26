@extends('layouts.app')
@section('title', 'Portfolio Analysis')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active">Portfolio</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-pie-chart me-2 text-primary"></i>Loan Portfolio Analysis</h1>
        <p class="page-subtitle">Total Outstanding Principal: <strong class="text-primary">{{ config('lms.currency_symbol') }}{{ number_format($totalPrincipal, 2) }}</strong></p>
    </div>
</div>

<div class="lms-card fade-in-up">
    <div class="lms-table-wrapper" style="border:none">
        <table class="lms-table">
            <thead>
                <tr><th>Loan No.</th><th>Borrower</th><th>Type</th><th>Approved Amount</th><th>Disbursed</th><th>Outstanding Principal</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($loans as $loan)
                <tr>
                    <td class="fw-bold text-primary">{{ $loan->loan_no }}</td>
                    <td>{{ $loan->customer->full_name ?? '—' }}</td>
                    <td>{{ ucfirst($loan->loan_type) }}</td>
                    <td>{{ config('lms.currency_symbol') }}{{ number_format($loan->approved_amount, 2) }}</td>
                    <td>{{ config('lms.currency_symbol') }}{{ number_format($loan->disbursed_amount, 2) }}</td>
                    <td class="fw-bold text-warning">{{ config('lms.currency_symbol') }}{{ number_format($loan->outstanding_principal, 2) }}</td>
                    <td><span class="badge-status badge-{{ \App\Enums\LoanStatus::from($loan->status)->color() }}">{{ \App\Enums\LoanStatus::from($loan->status)->label() }}</span></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No active portfolio loans found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
