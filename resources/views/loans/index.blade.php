@extends('layouts.app')
@section('title', 'Loans')
@section('breadcrumb')
<li class="breadcrumb-item active">Loans</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-cash-coin me-2 text-primary"></i>Loan Portfolio</h1>
        <p class="page-subtitle">Manage loan applications, approvals, disbursements, and repayments</p>
    </div>
    @can('loan.create')
    <a href="{{ route('loans.create') }}" class="btn-lms-primary"><i class="bi bi-plus-lg"></i>New Loan Application</a>
    @endcan
</div>

<div class="lms-card fade-in-up">
    <div class="lms-card-header">
        <div class="filter-row" style="margin-bottom:0; flex:1">
            <form method="GET" class="d-flex gap-2 flex-wrap" style="flex:1">
                <div class="search-box" style="flex:1; min-width:200px">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control" name="search" placeholder="Search loan no, customer name, mobile..." value="{{ request('search') }}">
                </div>
                <select class="form-select" name="status" style="width:160px">
                    <option value="">All Statuses</option>
                    @foreach(\App\Enums\LoanStatus::cases() as $st)
                    <option value="{{ $st->value }}" {{ request('status') == $st->value ? 'selected' : '' }}>{{ $st->label() }}</option>
                    @endforeach
                </select>
                <select class="form-select" name="loan_type" style="width:160px">
                    <option value="">All Types</option>
                    <option value="personal" {{ request('loan_type')=='personal'?'selected':'' }}>Personal Loan</option>
                    <option value="business" {{ request('loan_type')=='business'?'selected':'' }}>Business Loan</option>
                    <option value="vehicle" {{ request('loan_type')=='vehicle'?'selected':'' }}>Vehicle Loan</option>
                    <option value="mortgage" {{ request('loan_type')=='mortgage'?'selected':'' }}>Mortgage</option>
                    <option value="microfinance" {{ request('loan_type')=='microfinance'?'selected':'' }}>Microfinance</option>
                </select>
                <button type="submit" class="btn-lms-primary"><i class="bi bi-funnel"></i>Filter</button>
                @if(request()->hasAny(['search','status','loan_type']))
                <a href="{{ route('loans.index') }}" class="btn-lms-secondary"><i class="bi bi-x"></i>Clear</a>
                @endif
            </form>
        </div>
    </div>
    <div class="lms-table-wrapper" style="border:none; border-radius:0">
        <table class="lms-table">
            <thead>
                <tr>
                    <th>Loan No.</th>
                    <th>Customer</th>
                    <th>Branch</th>
                    <th>Applied / Approved</th>
                    <th>Rate & Tenure</th>
                    <th>Outstanding</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loans as $loan)
                <tr data-href="{{ route('loans.show', $loan) }}">
                    <td><span style="color:var(--primary-light);font-weight:600">{{ $loan->loan_no }}</span></td>
                    <td>
                        <div style="font-weight:600;color:var(--text-primary)">{{ $loan->customer->full_name ?? '—' }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $loan->customer->mobile ?? '' }}</div>
                    </td>
                    <td>{{ $loan->branch->name ?? '—' }}</td>
                    <td>
                        <div style="font-weight:600;color:var(--text-primary)">{{ config('lms.currency_symbol') }}{{ number_format($loan->applied_amount, 2) }}</div>
                        @if($loan->approved_amount)<div style="font-size:11px;color:var(--success)">Appr: {{ config('lms.currency_symbol') }}{{ number_format($loan->approved_amount, 2) }}</div>@endif
                    </td>
                    <td>
                        <div>{{ $loan->interest_rate }}% / yr</div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $loan->tenure_months }} Months ({{ ucfirst($loan->interest_type) }})</div>
                    </td>
                    <td>
                        <div class="fw-bold text-warning">{{ config('lms.currency_symbol') }}{{ number_format($loan->outstanding_principal, 2) }}</div>
                    </td>
                    <td>
                        <span class="badge-status badge-{{ \App\Enums\LoanStatus::from($loan->status)->color() }}">
                            {{ \App\Enums\LoanStatus::from($loan->status)->label() }}
                        </span>
                    </td>
                    <td onclick="event.stopPropagation()">
                        <div class="d-flex gap-1">
                            <a href="{{ route('loans.show', $loan) }}" class="btn-icon view" data-bs-toggle="tooltip" title="View"><i class="bi bi-eye"></i></a>
                            @if(in_array($loan->status, ['active', 'overdue', 'npa', 'restructured']))
                            <a href="{{ route('loans.payments.create', $loan) }}" class="btn-icon text-success" data-bs-toggle="tooltip" title="Collect Payment"><i class="bi bi-cash"></i></a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8"><div class="empty-state"><i class="bi bi-cash-coin"></i><h6>No loans found</h6></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center p-3">
        <span style="font-size:13px;color:var(--text-muted)">Showing {{ $loans->firstItem() }}–{{ $loans->lastItem() }} of {{ $loans->total() }} loans</span>
        {{ $loans->withQueryString()->links('partials.pagination') }}
    </div>
</div>
@endsection
