@extends('layouts.app')
@section('title', 'New Journal Voucher')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('accounting.index') }}">Accounting</a></li>
<li class="breadcrumb-item"><a href="{{ route('accounting.journal') }}">Journals</a></li>
<li class="breadcrumb-item active">New Voucher</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-journal-plus me-2 text-primary"></i>Post Journal Voucher</h1>
        <p class="page-subtitle">Double-entry accounting transaction voucher posting</p>
    </div>
    <a href="{{ route('accounting.journal') }}" class="btn-lms-secondary"><i class="bi bi-arrow-left"></i>Cancel</a>
</div>

<form method="POST" action="{{ route('accounting.journal.store') }}" class="fade-in-up">
    @csrf
    <div class="lms-card mb-4">
        <div class="lms-card-header"><h5 class="lms-card-title"><i class="bi bi-card-text text-primary"></i>Voucher Header</h5></div>
        <div class="lms-card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Posting Date <span class="text-danger">*</span></label>
                    <input type="date" name="entry_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Narration / Particulars <span class="text-danger">*</span></label>
                    <input type="text" name="narration" class="form-control" placeholder="Describe the transaction..." required>
                </div>
            </div>
        </div>
    </div>

    <div class="lms-card mb-4">
        <div class="lms-card-header d-flex justify-content-between">
            <h5 class="lms-card-title"><i class="bi bi-list-columns-reverse text-success"></i>Journal Lines</h5>
            <button type="button" class="btn-lms-secondary py-1 px-2" style="font-size:12px" onclick="addLine()"><i class="bi bi-plus"></i>Add Line</button>
        </div>
        <div class="lms-card-body p-0">
            <table class="lms-table" id="journalTable">
                <thead>
                    <tr>
                        <th width="40%">Account</th>
                        <th width="20%">Type</th>
                        <th width="25%">Amount (₹)</th>
                        <th width="15%">Action</th>
                    </tr>
                </thead>
                <tbody id="lineBody">
                    <tr>
                        <td>
                            <select name="lines[0][account_id]" class="form-select" required>
                                <option value="">Select Account</option>
                                @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }} ({{ ucfirst($acc->account_type) }})</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select name="lines[0][type]" class="form-select" required>
                                <option value="debit">DEBIT</option>
                                <option value="credit">CREDIT</option>
                            </select>
                        </td>
                        <td><input type="number" step="0.01" name="lines[0][amount]" class="form-control" required placeholder="0.00"></td>
                        <td><button type="button" class="btn-icon delete" onclick="this.closest('tr').remove()"><i class="bi bi-trash"></i></button></td>
                    </tr>
                    <tr>
                        <td>
                            <select name="lines[1][account_id]" class="form-select" required>
                                <option value="">Select Account</option>
                                @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }} ({{ ucfirst($acc->account_type) }})</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select name="lines[1][type]" class="form-select" required>
                                <option value="credit">CREDIT</option>
                                <option value="debit">DEBIT</option>
                            </select>
                        </td>
                        <td><input type="number" step="0.01" name="lines[1][amount]" class="form-control" required placeholder="0.00"></td>
                        <td><button type="button" class="btn-icon delete" onclick="this.closest('tr').remove()"><i class="bi bi-trash"></i></button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="lms-card-footer p-3 d-flex justify-content-end gap-2">
            <button type="submit" class="btn-lms-primary"><i class="bi bi-check-circle-fill"></i>Post Journal Voucher</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
let lineIndex = 2;
function addLine() {
    const tbody = document.getElementById('lineBody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            <select name="lines[${lineIndex}][account_id]" class="form-select" required>
                <option value="">Select Account</option>
                @foreach($accounts as $acc)
                <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }} ({{ ucfirst($acc->account_type) }})</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="lines[${lineIndex}][type]" class="form-select" required>
                <option value="debit">DEBIT</option>
                <option value="credit">CREDIT</option>
            </select>
        </td>
        <td><input type="number" step="0.01" name="lines[${lineIndex}][amount]" class="form-control" required placeholder="0.00"></td>
        <td><button type="button" class="btn-icon delete" onclick="this.closest('tr').remove()"><i class="bi bi-trash"></i></button></td>
    `;
    tbody.appendChild(tr);
    lineIndex++;
}
</script>
@endpush
