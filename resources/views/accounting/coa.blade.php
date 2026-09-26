@extends('layouts.app')
@section('title', 'Chart of Accounts')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('accounting.index') }}">Accounting</a></li>
<li class="breadcrumb-item active">Chart of Accounts</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-diagram-3 me-2 text-primary"></i>Chart of Accounts</h1>
        <p class="page-subtitle">Standard General Ledger account structure for NBFC & Microfinance operations</p>
    </div>
    @can('accounting.coa.manage')
    <button class="btn-lms-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal"><i class="bi bi-plus-lg"></i>Add Account</button>
    @endcan
</div>

<div class="lms-card fade-in-up">
    <div class="lms-table-wrapper" style="border:none">
        <table class="lms-table">
            <thead>
                <tr><th>Code</th><th>Account Name</th><th>Type</th><th>Sub Type</th><th>Normal Balance</th><th>System Account</th></tr>
            </thead>
            <tbody>
                @foreach($accounts as $parent)
                <tr class="fw-bold bg-dark text-white">
                    <td class="text-primary">{{ $parent->code }}</td>
                    <td>{{ $parent->name }}</td>
                    <td class="text-uppercase">{{ $parent->account_type }}</td>
                    <td>{{ ucfirst(str_replace('_',' ',$parent->account_sub_type)) }}</td>
                    <td class="text-uppercase">{{ $parent->normal_balance }}</td>
                    <td><span class="badge bg-secondary">Group</span></td>
                </tr>
                @foreach($parent->children as $child)
                <tr>
                    <td class="ps-4 text-info font-monospace">{{ $child->code }}</td>
                    <td class="ps-4">{{ $child->name }}</td>
                    <td class="text-uppercase text-muted" style="font-size:12px">{{ $child->account_type }}</td>
                    <td class="text-muted" style="font-size:12px">{{ ucfirst(str_replace('_',' ',$child->account_sub_type)) }}</td>
                    <td class="text-uppercase text-muted" style="font-size:12px">{{ $child->normal_balance }}</td>
                    <td>
                        @if($child->is_system_account)
                        <span class="badge bg-info" style="font-size:10px">System</span>
                        @else
                        <span class="badge bg-dark" style="font-size:10px">User</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Add Account Modal --}}
<div class="modal fade" id="addAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('accounting.coa.store') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Account to COA</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Account Code</label><input type="text" name="code" class="form-control" required placeholder="e.g. 1350"></div>
                    <div class="col-md-6"><label class="form-label">Account Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="col-md-6">
                        <label class="form-label">Account Type</label>
                        <select name="account_type" class="form-select" required>
                            <option value="asset">Asset</option>
                            <option value="liability">Liability</option>
                            <option value="equity">Equity</option>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Normal Balance</label>
                        <select name="normal_balance" class="form-select" required>
                            <option value="debit">Debit</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                    <div class="col-12"><label class="form-label">Sub Type Category</label><input type="text" name="account_sub_type" class="form-control" required placeholder="operating_expense, receivable..."></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-lms-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-lms-primary">Save Account</button></div>
        </form>
    </div>
</div>
@endsection
