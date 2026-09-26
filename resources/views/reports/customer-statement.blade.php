@extends('layouts.app')
@section('title', 'Customer Statement')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active">Customer Statement</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-person-lines-fill me-2 text-info"></i>Customer Account Statement</h1>
        <p class="page-subtitle">Full transaction history and loan balance summary for a customer</p>
    </div>
</div>

<div class="lms-card mb-4 fade-in-up">
    <div class="lms-card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label">Select Customer <span class="text-danger">*</span></label>
                <select name="customer_id" class="form-select" required>
                    <option value="">Select Customer</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->full_name }} ({{ $c->customer_no }}) — {{ $c->mobile }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn-lms-primary w-100"><i class="bi bi-search"></i>Generate Statement</button>
            </div>
        </form>
    </div>
</div>

@if($selectedCustomer)
<div class="lms-card fade-in-up">
    <div class="lms-card-header">
        <h5 class="lms-card-title"><i class="bi bi-file-text text-primary"></i>Statement for {{ $selectedCustomer->full_name }} ({{ $selectedCustomer->customer_no }})</h5>
    </div>
    <div class="lms-card-body">
        <h6 class="text-white fw-bold mb-3">Associated Loans</h6>
        @foreach($selectedCustomer->loans as $loan)
        <div class="p-3 mb-3 rounded" style="background:var(--bg-card-hover); border:1px solid var(--border)">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="fw-bold text-primary fs-6">{{ $loan->loan_no }}</span>
                    <span class="text-muted ms-2">Disbursed: {{ config('lms.currency_symbol') }}{{ number_format($loan->disbursed_amount, 2) }}</span>
                </div>
                <span class="badge-status badge-{{ \App\Enums\LoanStatus::from($loan->status)->color() }}">{{ \App\Enums\LoanStatus::from($loan->status)->label() }}</span>
            </div>
            <div class="mt-2 text-warning fw-semibold" style="font-size:13px">Outstanding Principal: {{ config('lms.currency_symbol') }}{{ number_format($loan->outstanding_principal, 2) }}</div>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
