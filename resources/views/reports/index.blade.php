@extends('layouts.app')
@section('title', 'Reports Center')
@section('breadcrumb')
<li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Reports Center</h1>
        <p class="page-subtitle">Generate portfolio analytics, disbursement logs, collection reports, and NPA statements</p>
    </div>
</div>

<div class="row g-4 fade-in-up">
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.disbursement') }}" class="lms-card p-4 d-block text-decoration-none h-100">
            <div class="stat-icon success mb-3" style="width:48px;height:48px;font-size:22px"><i class="bi bi-arrow-up-circle"></i></div>
            <h5 class="text-white fw-bold">Disbursement Report</h5>
            <p class="text-muted small mb-0">Detailed listing of loans disbursed with date filtering and payment modes.</p>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.collection') }}" class="lms-card p-4 d-block text-decoration-none h-100">
            <div class="stat-icon accent mb-3" style="width:48px;height:48px;font-size:22px"><i class="bi bi-wallet2"></i></div>
            <h5 class="text-white fw-bold">Collection Report</h5>
            <p class="text-muted small mb-0">Repayment transaction breakdown categorized by cashier and collection agent.</p>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.overdue') }}" class="lms-card p-4 d-block text-decoration-none h-100">
            <div class="stat-icon warning mb-3" style="width:48px;height:48px;font-size:22px"><i class="bi bi-exclamation-triangle"></i></div>
            <h5 class="text-white fw-bold">Overdue & Aging Report</h5>
            <p class="text-muted small mb-0">Instalments past due with DPD (Days Past Due) aging buckets (1-30, 31-60, 60+).</p>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.portfolio') }}" class="lms-card p-4 d-block text-decoration-none h-100">
            <div class="stat-icon primary mb-3" style="width:48px;height:48px;font-size:22px"><i class="bi bi-pie-chart"></i></div>
            <h5 class="text-white fw-bold">Portfolio Analysis</h5>
            <p class="text-muted small mb-0">Total active loan principal outstanding across branches and product lines.</p>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.customer-statement') }}" class="lms-card p-4 d-block text-decoration-none h-100">
            <div class="stat-icon info mb-3" style="width:48px;height:48px;font-size:22px"><i class="bi bi-person-lines-fill"></i></div>
            <h5 class="text-white fw-bold">Customer Account Statement</h5>
            <p class="text-muted small mb-0">Comprehensive transaction history statement for individual customer accounts.</p>
        </a>
    </div>
</div>
@endsection
