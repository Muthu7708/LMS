@extends('layouts.app')
@section('title', 'Balance Sheet')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('accounting.index') }}">Accounting</a></li>
<li class="breadcrumb-item active">Balance Sheet</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-bank me-2 text-primary"></i>Balance Sheet Statement</h1>
        <p class="page-subtitle">Statement of Financial Position: Assets = Liabilities + Equity</p>
    </div>
</div>

<div class="row g-4 fade-in-up">
    {{-- ASSETS --}}
    <div class="col-lg-6">
        <div class="lms-card h-100">
            <div class="lms-card-header"><h5 class="lms-card-title text-success"><i class="bi bi-wallet2 me-2"></i>ASSETS</h5></div>
            <div class="lms-card-body">
                <table class="table table-borderless table-sm text-secondary" style="font-size:14px">
                    @php $totAssets = 0; @endphp
                    @foreach($assetAccounts as $acc)
                    @php $val = $acc->journalLines->where('type','debit')->sum('amount') - $acc->journalLines->where('type','credit')->sum('amount'); $totAssets += $val; @endphp
                    <tr><td>{{ $acc->name }}</td><td class="text-end fw-semibold text-white">{{ config('lms.currency_symbol') }}{{ number_format($val, 2) }}</td></tr>
                    @endforeach
                    <tr class="border-top border-secondary fw-bold fs-5 text-success"><td>TOTAL ASSETS:</td><td class="text-end">{{ config('lms.currency_symbol') }}{{ number_format($totAssets, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- LIABILITIES & EQUITY --}}
    <div class="col-lg-6">
        <div class="lms-card h-100">
            <div class="lms-card-header"><h5 class="lms-card-title text-info"><i class="bi bi-pie-chart me-2"></i>LIABILITIES & EQUITY</h5></div>
            <div class="lms-card-body">
                <h6 class="text-warning fw-bold small uppercase">Liabilities</h6>
                <table class="table table-borderless table-sm text-secondary mb-3" style="font-size:14px">
                    @php $totLiab = 0; @endphp
                    @foreach($liabilityAccounts as $acc)
                    @php $val = $acc->journalLines->where('type','credit')->sum('amount') - $acc->journalLines->where('type','debit')->sum('amount'); $totLiab += $val; @endphp
                    <tr><td>{{ $acc->name }}</td><td class="text-end fw-semibold text-white">{{ config('lms.currency_symbol') }}{{ number_format($val, 2) }}</td></tr>
                    @endforeach
                </table>

                <h6 class="text-primary fw-bold small uppercase">Equity</h6>
                <table class="table table-borderless table-sm text-secondary" style="font-size:14px">
                    @php $totEq = 0; @endphp
                    @foreach($equityAccounts as $acc)
                    @php $val = $acc->journalLines->where('type','credit')->sum('amount') - $acc->journalLines->where('type','debit')->sum('amount'); $totEq += $val; @endphp
                    <tr><td>{{ $acc->name }}</td><td class="text-end fw-semibold text-white">{{ config('lms.currency_symbol') }}{{ number_format($val, 2) }}</td></tr>
                    @endforeach
                    <tr class="border-top border-secondary fw-bold fs-5 text-info"><td>TOTAL LIABILITIES & EQUITY:</td><td class="text-end">{{ config('lms.currency_symbol') }}{{ number_format($totLiab + $totEq, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
