@extends('layouts.app')
@section('title', 'Finance ERP — Accounting')
@section('breadcrumb')
<li class="breadcrumb-item active">Accounting</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-journal-bookmark-fill me-2 text-primary"></i>Financial Accounting ERP</h1>
        <p class="page-subtitle">Chart of Accounts, General Ledger, Double-Entry Journals, and Financial Statements</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('accounting.journal.create') }}" class="btn-lms-primary"><i class="bi bi-plus-lg"></i>New Journal Voucher</a>
    </div>
</div>

{{-- Navigation shortcuts --}}
<div class="row g-3 mb-4 fade-in-up">
    <div class="col-md-4 col-xl-2">
        <a href="{{ route('accounting.coa') }}" class="lms-card p-3 d-flex align-items-center gap-3 text-decoration-none h-100">
            <div class="stat-icon primary" style="width:40px;height:40px;font-size:18px"><i class="bi bi-diagram-3"></i></div>
            <div><div class="fw-bold text-white" style="font-size:13px">Chart of Accounts</div><div class="text-muted" style="font-size:11px">Manage Accounts</div></div>
        </a>
    </div>
    <div class="col-md-4 col-xl-2">
        <a href="{{ route('accounting.journal') }}" class="lms-card p-3 d-flex align-items-center gap-3 text-decoration-none h-100">
            <div class="stat-icon success" style="width:40px;height:40px;font-size:18px"><i class="bi bi-journal-text"></i></div>
            <div><div class="fw-bold text-white" style="font-size:13px">Journal Vouchers</div><div class="text-muted" style="font-size:11px">View Postings</div></div>
        </a>
    </div>
    <div class="col-md-4 col-xl-2">
        <a href="{{ route('accounting.ledger') }}" class="lms-card p-3 d-flex align-items-center gap-3 text-decoration-none h-100">
            <div class="stat-icon info" style="width:40px;height:40px;font-size:18px"><i class="bi bi-book"></i></div>
            <div><div class="fw-bold text-white" style="font-size:13px">General Ledger</div><div class="text-muted" style="font-size:11px">Account Statements</div></div>
        </a>
    </div>
    <div class="col-md-4 col-xl-2">
        <a href="{{ route('accounting.trial-balance') }}" class="lms-card p-3 d-flex align-items-center gap-3 text-decoration-none h-100">
            <div class="stat-icon warning" style="width:40px;height:40px;font-size:18px"><i class="bi bi-scale"></i></div>
            <div><div class="fw-bold text-white" style="font-size:13px">Trial Balance</div><div class="text-muted" style="font-size:11px">Debit/Credit Check</div></div>
        </a>
    </div>
    <div class="col-md-4 col-xl-2">
        <a href="{{ route('accounting.profit-loss') }}" class="lms-card p-3 d-flex align-items-center gap-3 text-decoration-none h-100">
            <div class="stat-icon accent" style="width:40px;height:40px;font-size:18px"><i class="bi bi-graph-up"></i></div>
            <div><div class="fw-bold text-white" style="font-size:13px">Profit & Loss</div><div class="text-muted" style="font-size:11px">Income Statement</div></div>
        </a>
    </div>
    <div class="col-md-4 col-xl-2">
        <a href="{{ route('accounting.balance-sheet') }}" class="lms-card p-3 d-flex align-items-center gap-3 text-decoration-none h-100">
            <div class="stat-icon primary" style="width:40px;height:40px;font-size:18px"><i class="bi bi-bank"></i></div>
            <div><div class="fw-bold text-white" style="font-size:13px">Balance Sheet</div><div class="text-muted" style="font-size:11px">Financial Position</div></div>
        </a>
    </div>
</div>

<div class="row g-4 fade-in-up">
    <div class="col-lg-12">
        <div class="lms-card">
            <div class="lms-card-header d-flex justify-content-between">
                <h5 class="lms-card-title"><i class="bi bi-clock-history text-primary"></i>Recent Journal Postings</h5>
                <a href="{{ route('accounting.journal') }}" class="btn-lms-secondary py-1 px-2" style="font-size:12px">View All Journals</a>
            </div>
            <div class="lms-table-wrapper" style="border:none">
                <table class="lms-table">
                    <thead>
                        <tr><th>Journal No.</th><th>Date</th><th>Narration</th><th>Debit (₹)</th><th>Credit (₹)</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentJournals as $j)
                        <tr>
                            <td class="fw-bold text-primary">{{ $j->journal_no }}</td>
                            <td>{{ $j->entry_date->format('d M Y') }}</td>
                            <td>{{ $j->narration }}</td>
                            <td class="fw-bold text-white">{{ number_format($j->total_debit, 2) }}</td>
                            <td class="fw-bold text-white">{{ number_format($j->total_credit, 2) }}</td>
                            <td><span class="badge-status badge-success">{{ ucfirst($j->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No journal entries recorded</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
