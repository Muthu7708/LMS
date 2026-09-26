@extends('layouts.app')
@section('title', 'Profit & Loss Statement')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('accounting.index') }}">Accounting</a></li>
<li class="breadcrumb-item active">Profit & Loss</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-graph-up me-2 text-accent"></i>Profit & Loss Statement (Income Statement)</h1>
        <p class="page-subtitle">Operating Revenue, Interest Income, Operating Expenses, and Net Profit</p>
    </div>
</div>

<div class="row g-4 fade-in-up">
    <div class="col-lg-6">
        <div class="lms-card h-100">
            <div class="lms-card-header"><h5 class="lms-card-title text-success"><i class="bi bi-arrow-up-circle me-2"></i>Revenue / Income</h5></div>
            <div class="lms-card-body">
                <table class="table table-borderless table-sm text-secondary" style="font-size:14px">
                    @php $totIncome = 0; @endphp
                    @foreach($incomeAccounts as $acc)
                    @php $inc = $acc->journalLines->where('type','credit')->sum('amount'); $totIncome += $inc; @endphp
                    <tr><td>{{ $acc->name }}</td><td class="text-end fw-semibold text-white">{{ config('lms.currency_symbol') }}{{ number_format($inc, 2) }}</td></tr>
                    @endforeach
                    <tr class="border-top border-secondary fw-bold fs-6 text-success"><td>Total Operating Income:</td><td class="text-end">{{ config('lms.currency_symbol') }}{{ number_format($totIncome, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="lms-card h-100">
            <div class="lms-card-header"><h5 class="lms-card-title text-danger"><i class="bi bi-arrow-down-circle me-2"></i>Operating Expenses</h5></div>
            <div class="lms-card-body">
                <table class="table table-borderless table-sm text-secondary" style="font-size:14px">
                    @php $totExpense = 0; @endphp
                    @foreach($expenseAccounts as $acc)
                    @php $exp = $acc->journalLines->where('type','debit')->sum('amount'); $totExpense += $exp; @endphp
                    <tr><td>{{ $acc->name }}</td><td class="text-end fw-semibold text-white">{{ config('lms.currency_symbol') }}{{ number_format($exp, 2) }}</td></tr>
                    @endforeach
                    <tr class="border-top border-secondary fw-bold fs-6 text-danger"><td>Total Expenses:</td><td class="text-end">{{ config('lms.currency_symbol') }}{{ number_format($totExpense, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="lms-card p-4 text-center">
            @php $netProfit = $totIncome - $totExpense; @endphp
            <div class="text-muted uppercase small">NET OPERATING PROFIT / (LOSS)</div>
            <div class="fs-2 fw-bold {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                {{ config('lms.currency_symbol') }}{{ number_format($netProfit, 2) }}
            </div>
        </div>
    </div>
</div>
@endsection
