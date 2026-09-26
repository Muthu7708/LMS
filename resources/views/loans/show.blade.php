@extends('layouts.app')
@section('title', 'Loan ' . $loan->loan_no)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('loans.index') }}">Loans</a></li>
<li class="breadcrumb-item active">{{ $loan->loan_no }}</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h1 class="page-title mb-0">{{ $loan->loan_no }}</h1>
            <span class="badge-status badge-{{ \App\Enums\LoanStatus::from($loan->status)->color() }}">
                {{ \App\Enums\LoanStatus::from($loan->status)->label() }}
            </span>
        </div>
        <p class="page-subtitle">Borrower: <strong>{{ $loan->customer->full_name ?? '—' }}</strong> | Branch: {{ $loan->branch->name ?? '—' }} | Type: {{ ucfirst($loan->loan_type) }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        {{-- WORKFLOW ACTION BUTTONS DEPENDING ON STATUS & PERMISSIONS --}}
        @if($loan->status === 'draft')
        @can('loan.submit')
        <form method="POST" action="{{ route('loans.submit', $loan) }}">
            @csrf
            <button type="submit" class="btn-lms-primary"><i class="bi bi-send-check"></i>Submit Application</button>
        </form>
        @endcan
        @endif

        @if(in_array($loan->status, ['submitted', 'under_review']))
        @can('loan.verify')
        <button class="btn-lms-secondary" data-bs-toggle="modal" data-bs-target="#verifyModal"><i class="bi bi-shield-check text-info"></i>Record Verification</button>
        @endcan
        @endif

        @if(in_array($loan->status, ['submitted', 'under_review', 'verified']))
        @can('loan.approve')
        <button class="btn-lms-primary" data-bs-toggle="modal" data-bs-target="#approveModal"><i class="bi bi-check-circle"></i>Approve Loan</button>
        <button class="btn-lms-danger" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="bi bi-x-circle"></i>Reject</button>
        @endcan
        @endif

        @if($loan->status === 'approved')
        @can('loan.disburse')
        <button class="btn-lms-primary" data-bs-toggle="modal" data-bs-target="#disburseModal"><i class="bi bi-cash-stack"></i>Disburse Funds</button>
        @endcan
        @endif

        @if(in_array($loan->status, ['active', 'overdue', 'npa', 'restructured']))
        @can('payment.collect')
        <a href="{{ route('loans.payments.create', $loan) }}" class="btn-lms-primary"><i class="bi bi-cash-coin"></i>Collect Payment</a>
        @endcan
        @can('loan.foreclosure')
        <a href="{{ route('loans.foreclosure.form', $loan) }}" class="btn-lms-secondary text-warning border-warning"><i class="bi bi-door-closed"></i>Foreclose Loan</a>
        @endcan
        @endif

        @if(in_array($loan->status, ['draft', 'submitted', 'under_review', 'verified']))
        @can('loan.edit')
        <a href="{{ route('loans.edit', $loan) }}" class="btn-lms-secondary"><i class="bi bi-pencil me-1"></i>Edit Application</a>
        @endcan
        @endif

        <a href="{{ route('loans.emi-schedule', $loan) }}" class="btn-lms-secondary"><i class="bi bi-calendar3"></i>View EMI Schedule</a>
    </div>
</div>

{{-- WORKFLOW STATUS PROGRESS BAR --}}
<div class="lms-card mb-4 fade-in-up">
    <div class="lms-card-body p-3">
        <div class="workflow-steps mb-0">
            @php
                $steps = ['draft', 'submitted', 'verified', 'approved', 'active', 'closed'];
                $currentIndex = array_search($loan->status, $steps);
                if ($currentIndex === false && in_array($loan->status, ['overdue', 'npa', 'restructured'])) $currentIndex = 4;
            @endphp
            @foreach($steps as $idx => $step)
            <div class="workflow-step">
                <div class="step-node">
                    <div class="step-circle {{ $idx < $currentIndex ? 'done' : ($idx === $currentIndex ? 'current' : '') }}">
                        @if($idx < $currentIndex)<i class="bi bi-check"></i>@else{{ $idx + 1 }}@endif
                    </div>
                    <span class="step-label">{{ ucfirst($step) }}</span>
                </div>
                @if(!$loop->last)
                <div class="step-connector {{ $idx < $currentIndex ? 'done' : '' }}"></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- FINANCIAL SUMMARY CARDS --}}
<div class="row g-3 mb-4 fade-in-up">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="bi bi-wallet2"></i></div>
            <div class="stat-content">
                <div class="stat-label">Disbursed Amount</div>
                <div class="stat-value">{{ config('lms.currency_symbol') }}{{ number_format($loan->disbursed_amount ?? $loan->applied_amount, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="bi bi-pie-chart"></i></div>
            <div class="stat-content">
                <div class="stat-label">Outstanding Principal</div>
                <div class="stat-value" style="color:#fbbf24">{{ config('lms.currency_symbol') }}{{ number_format($loan->outstanding_principal, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-content">
                <div class="stat-label">Total Principal Paid</div>
                <div class="stat-value" style="color:#34d399">{{ config('lms.currency_symbol') }}{{ number_format($loan->total_principal_paid, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon info"><i class="bi bi-receipt"></i></div>
            <div class="stat-content">
                <div class="stat-label">Total Interest Paid</div>
                <div class="stat-value">{{ config('lms.currency_symbol') }}{{ number_format($loan->total_interest_paid, 2) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- TABS --}}
<div class="lms-tabs mb-4 fade-in-up">
    <ul class="nav nav-tabs" id="loanTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-details"><i class="bi bi-info-circle me-2"></i>Loan Details</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-collateral"><i class="bi bi-shield-lock me-2"></i>Collaterals ({{ $loan->collaterals->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-guarantors"><i class="bi bi-people me-2"></i>Guarantors ({{ $loan->guarantors->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-payments"><i class="bi bi-receipt-cutoff me-2"></i>Payment History ({{ $loan->payments->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-approvals"><i class="bi bi-check2-all me-2"></i>Verifications & Approvals</button></li>
    </ul>
</div>

<div class="tab-content fade-in-up">
    {{-- DETAILS TAB --}}
    <div class="tab-pane fade show active" id="tab-details">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="lms-card h-100">
                    <div class="lms-card-header"><h5 class="lms-card-title"><i class="bi bi-file-earmark-text text-primary"></i>Terms & Parameters</h5></div>
                    <div class="lms-card-body">
                        <table class="table table-borderless table-sm text-secondary" style="font-size:13.5px">
                            <tr><td width="40%" class="text-muted">Applied Amount:</td><td class="fw-bold text-white">{{ config('lms.currency_symbol') }}{{ number_format($loan->applied_amount, 2) }}</td></tr>
                            <tr><td class="text-muted">Approved Amount:</td><td class="fw-bold text-success">{{ config('lms.currency_symbol') }}{{ number_format($loan->approved_amount ?? $loan->applied_amount, 2) }}</td></tr>
                            <tr><td class="text-muted">Interest Rate:</td><td>{{ $loan->interest_rate }}% per annum</td></tr>
                            <tr><td class="text-muted">Interest Calculation:</td><td class="text-capitalize">{{ $loan->interest_type }} Balance</td></tr>
                            <tr><td class="text-muted">Tenure:</td><td>{{ $loan->tenure_months }} Months</td></tr>
                            <tr><td class="text-muted">Repayment Frequency:</td><td class="text-capitalize">{{ $loan->repayment_frequency }}</td></tr>
                            <tr><td class="text-muted">Processing Fee:</td><td>{{ config('lms.currency_symbol') }}{{ number_format($loan->processing_fee_amount, 2) }}</td></tr>
                            <tr><td class="text-muted">Disbursement Date:</td><td>{{ $loan->disbursement_date ? $loan->disbursement_date->format('d M Y') : 'Pending' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="lms-card h-100">
                    <div class="lms-card-header"><h5 class="lms-card-title"><i class="bi bi-person-badge text-accent"></i>Borrower Details</h5></div>
                    <div class="lms-card-body">
                        <table class="table table-borderless table-sm text-secondary" style="font-size:13.5px">
                            <tr><td width="40%" class="text-muted">Name:</td><td><a href="{{ route('customers.show', $loan->customer) }}" class="fw-bold text-primary">{{ $loan->customer->full_name }}</a></td></tr>
                            <tr><td class="text-muted">Customer No:</td><td>{{ $loan->customer->customer_no }}</td></tr>
                            <tr><td class="text-muted">Mobile:</td><td>{{ $loan->customer->mobile }}</td></tr>
                            <tr><td class="text-muted">PAN:</td><td class="text-uppercase">{{ $loan->customer->pan ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Branch:</td><td>{{ $loan->branch->name ?? '—' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- COLLATERAL TAB --}}
    <div class="tab-pane fade" id="tab-collateral">
        <div class="lms-card">
            <div class="lms-card-header d-flex justify-content-between">
                <h5 class="lms-card-title"><i class="bi bi-shield-lock text-warning"></i>Collateral Assets</h5>
                <button class="btn-lms-secondary py-1 px-2" style="font-size:12px" data-bs-toggle="modal" data-bs-target="#addCollateralModal"><i class="bi bi-plus"></i>Add Collateral</button>
            </div>
            <div class="lms-table-wrapper" style="border:none">
                <table class="lms-table">
                    <thead>
                        <tr><th>Asset Type</th><th>Description</th><th>Owner</th><th>Estimated Value</th><th>Market Value</th></tr>
                    </thead>
                    <tbody>
                        @forelse($loan->collaterals as $c)
                        <tr>
                            <td class="fw-semibold text-white text-capitalize">{{ str_replace('_',' ',$c->collateral_type) }}</td>
                            <td>{{ $c->description }}</td>
                            <td>{{ $c->owner_name ?? 'Borrower' }}</td>
                            <td class="fw-bold text-success">{{ config('lms.currency_symbol') }}{{ number_format($c->estimated_value, 2) }}</td>
                            <td>{{ config('lms.currency_symbol') }}{{ number_format($c->market_value ?? $c->estimated_value, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No collateral assets added</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- GUARANTORS TAB --}}
    <div class="tab-pane fade" id="tab-guarantors">
        <div class="lms-card">
            <div class="lms-card-header d-flex justify-content-between">
                <h5 class="lms-card-title"><i class="bi bi-people text-info"></i>Loan Guarantors</h5>
                <button class="btn-lms-secondary py-1 px-2" style="font-size:12px" data-bs-toggle="modal" data-bs-target="#addGuarantorModal"><i class="bi bi-plus"></i>Add Guarantor</button>
            </div>
            <div class="lms-table-wrapper" style="border:none">
                <table class="lms-table">
                    <thead>
                        <tr><th>Name</th><th>Relationship</th><th>Mobile</th><th>PAN</th><th>Monthly Income</th></tr>
                    </thead>
                    <tbody>
                        @forelse($loan->guarantors as $g)
                        <tr>
                            <td class="fw-semibold text-white">{{ $g->name }}</td>
                            <td>{{ $g->relationship }}</td>
                            <td>{{ $g->mobile }}</td>
                            <td class="text-uppercase">{{ $g->pan ?? '—' }}</td>
                            <td class="fw-bold text-success">{{ config('lms.currency_symbol') }}{{ number_format($g->monthly_income ?? 0, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No guarantors added</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- PAYMENTS TAB --}}
    <div class="tab-pane fade" id="tab-payments">
        <div class="lms-card">
            <div class="lms-card-header"><h5 class="lms-card-title"><i class="bi bi-receipt-cutoff text-success"></i>Repayment Transactions</h5></div>
            <div class="lms-table-wrapper" style="border:none">
                <table class="lms-table">
                    <thead>
                        <tr><th>Receipt No</th><th>Date</th><th>Amount Paid</th><th>Principal</th><th>Interest</th><th>Mode</th><th>Collected By</th><th>Receipt</th></tr>
                    </thead>
                    <tbody>
                        @forelse($loan->payments as $p)
                        <tr class="{{ $p->is_reversed ? 'opacity-50 text-decoration-line-through' : '' }}">
                            <td class="fw-bold text-primary">{{ $p->receipt_no }}</td>
                            <td>{{ $p->payment_date->format('d M Y') }}</td>
                            <td class="fw-bold text-white">{{ config('lms.currency_symbol') }}{{ number_format($p->amount, 2) }}</td>
                            <td>{{ config('lms.currency_symbol') }}{{ number_format($p->principal_paid, 2) }}</td>
                            <td>{{ config('lms.currency_symbol') }}{{ number_format($p->interest_paid, 2) }}</td>
                            <td class="text-uppercase">{{ $p->payment_mode }}</td>
                            <td>{{ $p->collectedBy->name ?? 'System' }}</td>
                            <td><a href="{{ route('loans.payments.receipt', $p) }}" class="btn-icon view"><i class="bi bi-printer"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No repayments collected yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- APPROVALS TAB --}}
    <div class="tab-pane fade" id="tab-approvals">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="lms-card h-100">
                    <div class="lms-card-header"><h5 class="lms-card-title"><i class="bi bi-shield-check text-info"></i>Verifications</h5></div>
                    <div class="lms-card-body">
                        @forelse($loan->verifications as $v)
                        <div class="p-3 mb-2 rounded" style="background:var(--bg-card-hover); border:1px solid var(--border)">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold text-white">{{ ucfirst(str_replace('_',' ',$v->verification_type)) }}</span>
                                <span class="badge-status badge-{{ $v->status==='passed'?'success':($v->status==='failed'?'danger':'warning') }}">{{ ucfirst($v->status) }}</span>
                            </div>
                            <div class="text-muted" style="font-size:12px">By: {{ $v->verifiedBy->name ?? 'System' }} on {{ $v->visited_at->format('d M Y, h:i A') }}</div>
                            <div class="text-secondary mt-1" style="font-size:13px">{{ $v->remarks }}</div>
                        </div>
                        @empty
                        <div class="text-muted text-center py-4">No verifications recorded</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="lms-card h-100">
                    <div class="lms-card-header"><h5 class="lms-card-title"><i class="bi bi-check2-square text-success"></i>Approval Trail</h5></div>
                    <div class="lms-card-body">
                        @forelse($loan->approvals as $a)
                        <div class="p-3 mb-2 rounded" style="background:var(--bg-card-hover); border:1px solid var(--border)">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold text-white">{{ $a->approver_role }} (Level {{ $a->approval_level }})</span>
                                <span class="badge-status badge-{{ $a->action==='approved'?'success':'danger' }}">{{ ucfirst($a->action) }}</span>
                            </div>
                            <div class="text-muted" style="font-size:12px">By: {{ $a->approvedBy->name ?? 'System' }} on {{ $a->actioned_at->format('d M Y, h:i A') }}</div>
                            @if($a->approved_amount)<div class="text-success fw-semibold mt-1" style="font-size:13px">Approved Amount: {{ config('lms.currency_symbol') }}{{ number_format($a->approved_amount, 2) }} @ {{ $a->approved_rate }}%</div>@endif
                            @if($a->remarks)<div class="text-secondary mt-1" style="font-size:12px">Remarks: {{ $a->remarks }}</div>@endif
                        </div>
                        @empty
                        <div class="text-muted text-center py-4">No approval logs</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALS --}}
{{-- Verify Modal --}}
<div class="modal fade" id="verifyModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('loans.verify', $loan) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Record Verification</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Verification Type</label>
                    <select name="verification_type" class="form-select" required>
                        <option value="field">Field Visit</option>
                        <option value="telephonic">Telephonic Verification</option>
                        <option value="document">Document Verification</option>
                        <option value="residence">Residence Visit</option>
                        <option value="office">Office / Workplace Visit</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Verification Outcome</label>
                    <select name="status" class="form-select" required>
                        <option value="passed">Passed / Satisfactory</option>
                        <option value="failed">Failed / High Risk</option>
                        <option value="requires_further_review">Requires Further Review</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Verification Remarks <span class="text-danger">*</span></label>
                    <textarea name="remarks" class="form-control" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-lms-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-lms-primary">Submit Verification</button></div>
        </form>
    </div>
</div>

{{-- Approve Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('loans.approve', $loan) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title text-success"><i class="bi bi-check-circle me-2"></i>Approve Loan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Approved Amount (₹)</label><input type="number" step="0.01" name="approved_amount" class="form-control" value="{{ $loan->applied_amount }}" required></div>
                    <div class="col-md-6"><label class="form-label">Approved Interest Rate (%)</label><input type="number" step="0.01" name="approved_rate" class="form-control" value="{{ $loan->interest_rate }}" required></div>
                    <div class="col-md-6"><label class="form-label">Approved Tenure (Months)</label><input type="number" name="approved_tenure" class="form-control" value="{{ $loan->tenure_months }}" required></div>
                    <div class="col-12"><label class="form-label">Approval Conditions</label><textarea name="conditions" class="form-control" rows="2" placeholder="e.g. Subject to PDC cheques submission"></textarea></div>
                    <div class="col-12"><label class="form-label">Approver Remarks</label><textarea name="remarks" class="form-control" rows="2"></textarea></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-lms-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-lms-primary">Approve Application</button></div>
        </form>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('loans.reject', $loan) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title text-danger"><i class="bi bi-x-circle me-2"></i>Reject Application</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" rows="3" required placeholder="Specify why the application is rejected..."></textarea>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-lms-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-lms-danger">Confirm Rejection</button></div>
        </form>
    </div>
</div>

{{-- Disburse Modal --}}
<div class="modal fade" id="disburseModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('loans.disburse', $loan) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title text-primary"><i class="bi bi-cash-stack me-2"></i>Disburse Funds</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Disbursement Amount (₹)</label><input type="number" step="0.01" name="amount" class="form-control" value="{{ $loan->approved_amount ?? $loan->applied_amount }}" required></div>
                    <div class="col-md-6"><label class="form-label">Disbursement Date</label><input type="date" name="disbursement_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Mode</label>
                        <select name="mode" class="form-select" required>
                            <option value="neft">NEFT / RTGS</option>
                            <option value="cheque">Cheque</option>
                            <option value="cash">Cash</option>
                            <option value="upi">UPI / IMPS</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Bank Name</label><input type="text" name="bank_name" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Account Number</label><input type="text" name="account_number" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Reference No / UTR</label><input type="text" name="reference_no" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-lms-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-lms-primary">Disburse & Generate Schedule</button></div>
        </form>
    </div>
</div>

{{-- Add Collateral Modal --}}
<div class="modal fade" id="addCollateralModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('loans.collaterals.store', $loan) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Collateral Asset</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Asset Type</label>
                        <select name="collateral_type" class="form-select" required>
                            <option value="property">Real Estate / Property</option>
                            <option value="vehicle">Vehicle</option>
                            <option value="gold">Gold / Jewelry</option>
                            <option value="fd">Fixed Deposit (FD)</option>
                            <option value="shares">Stocks / Shares</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Owner Name</label><input type="text" name="owner_name" class="form-control" value="{{ $loan->customer->full_name }}"></div>
                    <div class="col-12"><label class="form-label">Description</label><input type="text" name="description" class="form-control" required placeholder="Registration no, address, weight etc."></div>
                    <div class="col-md-6"><label class="form-label">Estimated Value (₹)</label><input type="number" step="0.01" name="estimated_value" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Market Value (₹)</label><input type="number" step="0.01" name="market_value" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-lms-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-lms-primary">Save Collateral</button></div>
        </form>
    </div>
</div>

{{-- Add Guarantor Modal --}}
<div class="modal fade" id="addGuarantorModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('loans.guarantors.store', $loan) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Loan Guarantor</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Guarantor Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Relationship</label><input type="text" name="relationship" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Mobile Number</label><input type="text" name="mobile" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">PAN Number</label><input type="text" name="pan" class="form-control text-uppercase" maxlength="10"></div>
                    <div class="col-md-6"><label class="form-label">Monthly Income (₹)</label><input type="number" step="0.01" name="monthly_income" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-lms-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-lms-primary">Save Guarantor</button></div>
        </form>
    </div>
</div>
@endsection
