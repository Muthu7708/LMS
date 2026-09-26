@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">
            <i class="bi bi-grid-1x2-fill me-2 text-primary"></i>Dashboard
        </h1>
        <p class="page-subtitle">Welcome back, {{ auth()->user()->name }}! Here's your portfolio overview.</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-secondary">
            <i class="bi bi-clock me-1"></i>{{ now()->format('d M Y, h:i A') }}
        </span>
    </div>
</div>

{{-- ── KPI STATS ──────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3 fade-in-up">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="bi bi-cash-coin"></i></div>
            <div class="stat-content">
                <div class="stat-label">Active Loans</div>
                <div class="stat-value" data-count="{{ $kpis['total_active_loans'] }}">{{ number_format($kpis['total_active_loans']) }}</div>
                <div class="stat-change up"><i class="bi bi-arrow-up-short"></i>Portfolio</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 fade-in-up">
        <div class="stat-card">
            <div class="stat-icon success"><i class="bi bi-arrow-up-circle-fill"></i></div>
            <div class="stat-content">
                <div class="stat-label">Disbursed Today</div>
                <div class="stat-value" data-count="₹{{ number_format($kpis['total_disbursed_today'], 2) }}">
                    {{ config('lms.currency_symbol') }}{{ number_format($kpis['total_disbursed_today'], 2) }}
                </div>
                <div class="stat-change up"><i class="bi bi-calendar-day"></i>Today</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 fade-in-up">
        <div class="stat-card">
            <div class="stat-icon accent"><i class="bi bi-wallet2"></i></div>
            <div class="stat-content">
                <div class="stat-label">Collected Today</div>
                <div class="stat-value">
                    {{ config('lms.currency_symbol') }}{{ number_format($kpis['total_collected_today'], 2) }}
                </div>
                <div class="stat-change up"><i class="bi bi-receipt"></i>Payments</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 fade-in-up">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="stat-content">
                <div class="stat-label">Overdue Loans</div>
                <div class="stat-value" style="color: #fbbf24" data-count="{{ $kpis['total_overdue_loans'] }}">{{ number_format($kpis['total_overdue_loans']) }}</div>
                <div class="stat-change down">
                    @if($kpis['total_npa_loans']) +{{ $kpis['total_npa_loans'] }} NPA @else Healthy @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3 fade-in-up">
        <div class="stat-card">
            <div class="stat-icon info"><i class="bi bi-people-fill"></i></div>
            <div class="stat-content">
                <div class="stat-label">Active Customers</div>
                <div class="stat-value" data-count="{{ $kpis['total_customers'] }}">{{ number_format($kpis['total_customers']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 fade-in-up">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="bi bi-pie-chart-fill"></i></div>
            <div class="stat-content">
                <div class="stat-label">Total Portfolio</div>
                <div class="stat-value" style="font-size:18px">
                    {{ config('lms.currency_symbol') }}{{ number_format($kpis['total_portfolio'] / 100000, 2) }}L
                </div>
                <div class="stat-change">Outstanding Principal</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 fade-in-up">
        <div class="stat-card">
            <div class="stat-icon success"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-content">
                <div class="stat-label">Collection This Month</div>
                <div class="stat-value" style="font-size:18px">
                    {{ config('lms.currency_symbol') }}{{ number_format($kpis['collection_this_month'], 2) }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 fade-in-up">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-content">
                <div class="stat-label">Pending Approvals</div>
                <div class="stat-value" style="color: #fbbf24">{{ $kpis['pending_approval'] }}</div>
                <div class="stat-change">
                    <a href="{{ route('loans.index') }}?status=submitted" class="text-decoration-none" style="color:#fbbf24">Review Now →</a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── CHARTS ROW ─────────────────────────────────────────────────── --}}
<div class="row g-4 mb-4">
    {{-- Disbursement Trend --}}
    <div class="col-lg-8">
        <div class="lms-card h-100">
            <div class="lms-card-header">
                <h5 class="lms-card-title">
                    <i class="bi bi-bar-chart-line text-primary"></i>
                    Disbursement Trend (Last 6 Months)
                </h5>
            </div>
            <div class="lms-card-body">
                <canvas id="disbursementChart" height="120"></canvas>
            </div>
        </div>
    </div>

    {{-- Loan Status Pie --}}
    <div class="col-lg-4">
        <div class="lms-card h-100">
            <div class="lms-card-header">
                <h5 class="lms-card-title">
                    <i class="bi bi-pie-chart text-accent"></i>
                    Loan Status Breakdown
                </h5>
            </div>
            <div class="lms-card-body">
                <canvas id="statusChart" height="200"></canvas>
                <div class="mt-3">
                    @foreach($statusBreakdown->take(5) as $item)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span style="font-size:12px; color:var(--text-secondary)">{{ ucfirst(str_replace('_',' ',$item->status)) }}</span>
                        <span class="badge-status badge-{{ \App\Enums\LoanStatus::from($item->status)->color() }}">{{ $item->total }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── RECENT LOANS & OVERDUE EMIS ───────────────────────────────── --}}
<div class="row g-4">
    {{-- Recent Loans --}}
    <div class="col-lg-7">
        <div class="lms-card">
            <div class="lms-card-header">
                <h5 class="lms-card-title">
                    <i class="bi bi-clock-history text-info"></i>
                    Recent Loans
                </h5>
                <a href="{{ route('loans.index') }}" class="btn-lms-secondary" style="padding:5px 12px; font-size:12px">View All</a>
            </div>
            <div class="lms-table-wrapper" style="border:none; border-radius:0">
                <table class="lms-table">
                    <thead>
                        <tr>
                            <th>Loan No.</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLoans as $loan)
                        <tr data-href="{{ route('loans.show', $loan) }}" style="cursor:pointer">
                            <td><span style="color:var(--primary-light); font-weight:600">{{ $loan->loan_no }}</span></td>
                            <td>
                                <div style="font-weight:500; color:var(--text-primary)">{{ $loan->customer->full_name ?? 'N/A' }}</div>
                                <div style="font-size:11px; color:var(--text-muted)">{{ $loan->branch->name ?? '' }}</div>
                            </td>
                            <td><span style="text-transform:capitalize; font-size:12px">{{ str_replace('_',' ',$loan->loan_type) }}</span></td>
                            <td style="font-weight:600">{{ config('lms.currency_symbol') }}{{ number_format($loan->applied_amount, 2) }}</td>
                            <td>
                                <span class="badge-status badge-{{ \App\Enums\LoanStatus::from($loan->status)->color() }}">
                                    {{ \App\Enums\LoanStatus::from($loan->status)->label() }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center" style="padding:40px; color:var(--text-muted)">No loans yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Overdue EMIs --}}
    <div class="col-lg-5">
        <div class="lms-card">
            <div class="lms-card-header">
                <h5 class="lms-card-title">
                    <i class="bi bi-alarm-fill text-warning"></i>
                    Overdue EMIs
                </h5>
                <span class="badge-status badge-warning">{{ $overdueEmis->count() }}</span>
            </div>
            <div style="max-height:380px; overflow-y:auto">
                @forelse($overdueEmis as $emi)
                <div class="d-flex align-items-center gap-3 p-3" style="border-bottom:1px solid var(--border)">
                    <div class="stat-icon warning" style="width:40px;height:40px;font-size:16px;flex-shrink:0">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div style="font-size:13px;font-weight:600;color:var(--text-primary)">
                            {{ $emi->loan->customer->full_name ?? 'N/A' }}
                        </div>
                        <div style="font-size:11px;color:var(--text-muted)">
                            {{ $emi->loan->loan_no }} · EMI #{{ $emi->emi_number }} · Due {{ $emi->due_date->format('d M Y') }}
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-size:13px;font-weight:700;color:#fbbf24">
                            {{ config('lms.currency_symbol') }}{{ number_format($emi->emi_amount - $emi->paid_amount, 2) }}
                        </div>
                        <div style="font-size:11px;color:#f87171">
                            {{ $emi->due_date->diffInDays() }} days
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-state" style="padding:40px">
                    <i class="bi bi-check-circle-fill" style="color:#10b981"></i>
                    <h6 style="color:#34d399; margin-top:12px">All EMIs on time!</h6>
                    <p>No overdue EMIs at the moment.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Chart colours
const c = {
    primary: '#6366f1', accent: '#06b6d4',
    success: '#10b981', warning: '#f59e0b',
    danger: '#ef4444', info: '#3b82f6',
};

Chart.defaults.color = '#64748b';
Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.font.size = 12;

// Disbursement Trend
const disbData = @json($disbursementTrend);
new Chart(document.getElementById('disbursementChart'), {
    type: 'bar',
    data: {
        labels: disbData.map(d => d.month),
        datasets: [{
            label: 'Disbursed (₹)',
            data: disbData.map(d => d.total),
            backgroundColor: 'rgba(99,102,241,0.35)',
            borderColor: c.primary,
            borderWidth: 2,
            borderRadius: 6,
            hoverBackgroundColor: 'rgba(99,102,241,0.6)',
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => '₹' + parseFloat(ctx.raw).toLocaleString('en-IN', {minimumFractionDigits: 2})
                }
            }
        },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#64748b' } },
            y: {
                grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#64748b',
                    callback: v => '₹' + (v/100000).toFixed(1) + 'L'
                }
            }
        }
    }
});

// Status Pie
const statusColors = {
    draft: '#64748b', submitted: '#3b82f6', under_review: '#8b5cf6',
    verified: '#06b6d4', approved: '#10b981', rejected: '#ef4444',
    disbursed: '#10b981', active: '#10b981', overdue: '#f59e0b',
    npa: '#ef4444', closed: '#64748b', restructured: '#f59e0b',
};

const sData = @json($statusBreakdown);
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: sData.map(d => d.status.replace(/_/g, ' ').toUpperCase()),
        datasets: [{
            data: sData.map(d => d.total),
            backgroundColor: sData.map(d => statusColors[d.status] ?? '#64748b'),
            borderColor: '#1e2433',
            borderWidth: 3,
            hoverOffset: 8,
        }]
    },
    options: {
        responsive: true,
        cutout: '70%',
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.raw } }
        }
    }
});
</script>
@endpush
