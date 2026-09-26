@extends('layouts.app')
@section('title', 'General Ledger')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('accounting.index') }}">Accounting</a></li>
<li class="breadcrumb-item active">General Ledger</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-book me-2 text-primary"></i>General Ledger Statement</h1>
        <p class="page-subtitle">Account ledger statements and running balance tracking</p>
    </div>
</div>

<div class="lms-card mb-4 fade-in-up">
    <div class="lms-card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Select GL Account <span class="text-danger">*</span></label>
                <select name="account_id" class="form-select" required>
                    <option value="">Select Account</option>
                    @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>
                        {{ $acc->code }} — {{ $acc->name }} ({{ ucfirst($acc->account_type) }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn-lms-primary w-100"><i class="bi bi-search"></i>Generate Statement</button>
            </div>
        </form>
    </div>
</div>

@if($selectedAccount)
<div class="lms-card fade-in-up">
    <div class="lms-card-header d-flex justify-content-between">
        <h5 class="lms-card-title"><i class="bi bi-journal text-info"></i>Ledger Statement: {{ $selectedAccount->code }} — {{ $selectedAccount->name }}</h5>
        <span class="badge bg-primary">Normal Balance: {{ strtoupper($selectedAccount->normal_balance) }}</span>
    </div>
    <div class="lms-table-wrapper" style="border:none">
        <table class="lms-table">
            <thead>
                <tr><th>Date</th><th>Voucher No</th><th>Particulars / Narration</th><th>Debit (₹)</th><th>Credit (₹)</th></tr>
            </thead>
            <tbody>
                @forelse($lines as $line)
                <tr>
                    <td>{{ $line->journalEntry->entry_date->format('d M Y') }}</td>
                    <td class="fw-bold text-primary">{{ $line->journalEntry->journal_no }}</td>
                    <td>{{ $line->journalEntry->narration }}</td>
                    <td class="fw-semibold {{ $line->type==='debit'?'text-success':'' }}">{{ $line->type === 'debit' ? number_format($line->amount, 2) : '—' }}</td>
                    <td class="fw-semibold {{ $line->type==='credit'?'text-info':'' }}">{{ $line->type === 'credit' ? number_format($line->amount, 2) : '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No transactions found for this account</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
