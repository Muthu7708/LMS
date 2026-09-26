@extends('layouts.app')
@section('title', 'Journal Vouchers')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('accounting.index') }}">Accounting</a></li>
<li class="breadcrumb-item active">Journals</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-journal-text me-2 text-primary"></i>Journal Vouchers</h1>
        <p class="page-subtitle">All general ledger double-entry transactions</p>
    </div>
    @can('accounting.journal.create')
    <a href="{{ route('accounting.journal.create') }}" class="btn-lms-primary"><i class="bi bi-plus-lg"></i>New Journal Voucher</a>
    @endcan
</div>

<div class="lms-card fade-in-up">
    <div class="lms-table-wrapper" style="border:none">
        <table class="lms-table">
            <thead>
                <tr><th>Journal No.</th><th>Date</th><th>Narration</th><th>Entries</th><th>Total Debit</th><th>Total Credit</th><th>Posted By</th></tr>
            </thead>
            <tbody>
                @forelse($journals as $j)
                <tr>
                    <td class="fw-bold text-primary">{{ $j->journal_no }}</td>
                    <td>{{ $j->entry_date->format('d M Y') }}</td>
                    <td>{{ $j->narration }}</td>
                    <td>
                        <ul class="list-unstyled mb-0" style="font-size:12px">
                            @foreach($j->lines as $line)
                            <li class="{{ $line->type==='debit'?'text-success':'text-info' }}">
                                {{ $line->type==='debit'?'Dr':'Cr' }} {{ $line->account->name ?? 'Account' }}: {{ config('lms.currency_symbol') }}{{ number_format($line->amount, 2) }}
                            </li>
                            @endforeach
                        </ul>
                    </td>
                    <td class="fw-bold text-white">{{ config('lms.currency_symbol') }}{{ number_format($j->total_debit, 2) }}</td>
                    <td class="fw-bold text-white">{{ config('lms.currency_symbol') }}{{ number_format($j->total_credit, 2) }}</td>
                    <td>{{ $j->createdBy->name ?? 'System' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No journal entries found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center p-3">
        <span style="font-size:13px;color:var(--text-muted)">Showing {{ $journals->firstItem() }}–{{ $journals->lastItem() }} of {{ $journals->total() }} journals</span>
        {{ $journals->links('partials.pagination') }}
    </div>
</div>
@endsection
