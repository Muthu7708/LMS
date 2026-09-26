@extends('layouts.app')
@section('title', 'EMI Schedule — ' . $loan->loan_no)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('loans.index') }}">Loans</a></li>
<li class="breadcrumb-item"><a href="{{ route('loans.show', $loan) }}">{{ $loan->loan_no }}</a></li>
<li class="breadcrumb-item active">EMI Schedule</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-calendar3 me-2 text-primary"></i>EMI Repayment Schedule</h1>
        <p class="page-subtitle">Loan No: <strong class="text-primary">{{ $loan->loan_no }}</strong> | Borrower: {{ $loan->customer->full_name ?? '—' }} | Disbursed: {{ config('lms.currency_symbol') }}{{ number_format($loan->disbursed_amount, 2) }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('loans.show', $loan) }}" class="btn-lms-secondary"><i class="bi bi-arrow-left"></i>Back to Loan</a>
        @if(in_array($loan->status, ['active', 'overdue', 'npa', 'restructured']))
        @can('payment.collect')
        <a href="{{ route('loans.payments.create', $loan) }}" class="btn-lms-primary"><i class="bi bi-cash-coin"></i>Collect Payment</a>
        @endcan
        @endif
    </div>
</div>

<div class="lms-card fade-in-up">
    <div class="lms-card-header d-flex justify-content-between">
        <h5 class="lms-card-title"><i class="bi bi-list-ol text-success"></i>Amortization Schedule ({{ $schedules->count() }} Instalments)</h5>
        <span class="badge bg-primary">{{ ucfirst($loan->interest_type) }} Rate @ {{ $loan->interest_rate }}%</span>
    </div>
    <div class="lms-table-wrapper" style="border:none">
        <table class="lms-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Due Date</th>
                    <th>Opening Principal</th>
                    <th>EMI Amount</th>
                    <th>Principal Part</th>
                    <th>Interest Part</th>
                    <th>Closing Principal</th>
                    <th>Paid Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $s)
                <tr class="{{ $s->status === 'paid' ? 'opacity-75' : ($s->isOverdue() ? 'bg-danger-subtle' : '') }}">
                    <td class="fw-bold">{{ $s->emi_number }}</td>
                    <td class="fw-semibold text-white">{{ $s->due_date->format('d M Y') }}</td>
                    <td>{{ config('lms.currency_symbol') }}{{ number_format($s->opening_balance, 2) }}</td>
                    <td class="fw-bold text-white">{{ config('lms.currency_symbol') }}{{ number_format($s->emi_amount, 2) }}</td>
                    <td>{{ config('lms.currency_symbol') }}{{ number_format($s->principal_amount, 2) }}</td>
                    <td>{{ config('lms.currency_symbol') }}{{ number_format($s->interest_amount, 2) }}</td>
                    <td>{{ config('lms.currency_symbol') }}{{ number_format($s->closing_balance, 2) }}</td>
                    <td class="fw-bold text-success">{{ config('lms.currency_symbol') }}{{ number_format($s->paid_amount, 2) }}</td>
                    <td>
                        @if($s->status === 'paid')
                            <span class="badge-status badge-success">Paid</span>
                        @elseif($s->isOverdue())
                            <span class="badge-status badge-danger">Overdue ({{ $s->due_date->diffInDays() }}d)</span>
                        @elseif($s->status === 'partial')
                            <span class="badge-status badge-warning">Partial</span>
                        @else
                            <span class="badge-status badge-secondary">Pending</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No EMI schedule generated yet. Disburse the loan to generate schedule.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
