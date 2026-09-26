@extends('layouts.app')
@section('title', 'Trial Balance')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('accounting.index') }}">Accounting</a></li>
<li class="breadcrumb-item active">Trial Balance</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-scale me-2 text-warning"></i>Trial Balance Statement</h1>
        <p class="page-subtitle">Verification of debit and credit balance equality across all GL accounts</p>
    </div>
</div>

<div class="lms-card fade-in-up">
    <div class="lms-table-wrapper" style="border:none">
        <table class="lms-table">
            <thead>
                <tr><th>Code</th><th>Account Name</th><th>Type</th><th class="text-end">Debit (₹)</th><th class="text-end">Credit (₹)</th></tr>
            </thead>
            <tbody>
                @php $totDeb = 0; $totCred = 0; @endphp
                @foreach($accounts as $acc)
                @php
                    $deb  = $acc->journalLines->where('type','debit')->sum('amount');
                    $cred = $acc->journalLines->where('type','credit')->sum('amount');
                    $totDeb += $deb; $totCred += $cred;
                @endphp
                @if($deb > 0 || $cred > 0)
                <tr>
                    <td class="fw-bold text-primary">{{ $acc->code }}</td>
                    <td class="fw-semibold text-white">{{ $acc->name }}</td>
                    <td class="text-uppercase text-muted" style="font-size:12px">{{ $acc->account_type }}</td>
                    <td class="text-end fw-semibold text-success">{{ $deb > 0 ? number_format($deb, 2) : '—' }}</td>
                    <td class="text-end fw-semibold text-info">{{ $cred > 0 ? number_format($cred, 2) : '—' }}</td>
                </tr>
                @endif
                @endforeach
                <tr class="table-dark text-white fw-bold fs-6">
                    <td colspan="3">TOTALS</td>
                    <td class="text-end text-success">{{ number_format($totDeb, 2) }}</td>
                    <td class="text-end text-info">{{ number_format($totCred, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
