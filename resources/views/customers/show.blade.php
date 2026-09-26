@extends('layouts.app')
@section('title', 'Customer Detail — ' . $customer->full_name)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
<li class="breadcrumb-item active">{{ $customer->customer_no }}</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h1 class="page-title mb-0">{{ $customer->full_name }}</h1>
            <span class="badge-status badge-{{ $customer->status === 'active' ? 'success' : ($customer->status === 'blacklisted' ? 'danger' : 'secondary') }}">
                {{ ucfirst($customer->status) }}
            </span>
        </div>
        <p class="page-subtitle">Customer No: <strong class="text-primary">{{ $customer->customer_no }}</strong> | Branch: {{ $customer->branch->name ?? '—' }} | Added: {{ $customer->created_at->format('d M Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        @can('customer.edit')
        <a href="{{ route('customers.edit', $customer) }}" class="btn-lms-secondary"><i class="bi bi-pencil"></i>Edit Profile</a>
        @endcan
        @can('loan.create')
        <a href="{{ route('loans.create', ['customer_id' => $customer->id]) }}" class="btn-lms-primary"><i class="bi bi-cash-coin"></i>New Loan Application</a>
        @endcan
        @if($customer->status !== 'blacklisted')
        @can('customer.blacklist')
        <button class="btn-lms-danger" data-bs-toggle="modal" data-bs-target="#blacklistModal"><i class="bi bi-slash-circle"></i>Blacklist</button>
        @endcan
        @else
        @can('customer.unblacklist')
        <form method="POST" action="{{ route('customers.unblacklist', $customer) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn-lms-secondary text-success border-success" onclick="return confirm('Reinstate this customer?')"><i class="bi bi-check-circle"></i>Unblacklist</button>
        </form>
        @endcan
        @endif
    </div>
</div>

@if($customer->status === 'blacklisted')
<div class="alert alert-danger fade-in-up">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Customer Blacklisted:</strong> {{ $customer->blacklist_reason ?? 'No reason provided' }}
</div>
@endif

{{-- Customer Tabs --}}
<div class="lms-tabs mb-4 fade-in-up">
    <ul class="nav nav-tabs" id="customerTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-overview"><i class="bi bi-person-badge me-2"></i>Overview</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-kyc"><i class="bi bi-shield-check me-2"></i>KYC & Documents</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-employment"><i class="bi bi-briefcase me-2"></i>Employment & Bank</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-nominees"><i class="bi bi-people me-2"></i>Nominees & References</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-loans"><i class="bi bi-cash-stack me-2"></i>Loan History ({{ $customer->loans->count() }})</button></li>
    </ul>
</div>

<div class="tab-content fade-in-up">
    {{-- OVERVIEW TAB --}}
    <div class="tab-pane fade show active" id="tab-overview">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="lms-card h-100">
                    <div class="lms-card-header"><h5 class="lms-card-title"><i class="bi bi-card-heading text-primary"></i>Personal Details</h5></div>
                    <div class="lms-card-body">
                        <table class="table table-borderless table-sm text-secondary" style="font-size:13.5px">
                            <tr><td width="35%" class="text-muted">Full Name:</td><td class="fw-semibold text-white">{{ $customer->full_name }}</td></tr>
                            <tr><td class="text-muted">Mobile:</td><td>{{ $customer->mobile }}</td></tr>
                            <tr><td class="text-muted">Email:</td><td>{{ $customer->email ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Gender:</td><td>{{ ucfirst($customer->gender ?? '—') }}</td></tr>
                            <tr><td class="text-muted">Date of Birth:</td><td>{{ $customer->date_of_birth ? $customer->date_of_birth->format('d M Y') : '—' }}</td></tr>
                            <tr><td class="text-muted">PAN:</td><td class="fw-bold text-uppercase">{{ $customer->pan ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Aadhaar:</td><td>{{ $customer->aadhaar ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Marital Status:</td><td>{{ ucfirst($customer->marital_status ?? '—') }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="lms-card h-100">
                    <div class="lms-card-header d-flex justify-content-between">
                        <h5 class="lms-card-title"><i class="bi bi-geo-alt text-accent"></i>Addresses</h5>
                        <button class="btn-lms-secondary py-1 px-2" style="font-size:12px" data-bs-toggle="modal" data-bs-target="#addAddressModal"><i class="bi bi-plus"></i>Add Address</button>
                    </div>
                    <div class="lms-card-body">
                        @forelse($customer->addresses as $addr)
                        <div class="p-3 mb-2 rounded" style="background:var(--bg-card-hover); border:1px solid var(--border)">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="badge bg-primary text-uppercase" style="font-size:10px">{{ $addr->type }}</span>
                                @if($addr->is_primary)<span class="badge bg-success" style="font-size:10px">Primary</span>@endif
                            </div>
                            <div class="text-white fw-medium" style="font-size:13px">{{ $addr->address_line1 }}</div>
                            @if($addr->address_line2)<div class="text-muted" style="font-size:12px">{{ $addr->address_line2 }}</div>@endif
                            <div class="text-muted" style="font-size:12px">{{ $addr->city }}, {{ $addr->state }} — {{ $addr->pin_code }}</div>
                        </div>
                        @empty
                        <div class="text-muted text-center py-4" style="font-size:13px">No addresses added</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KYC TAB --}}
    <div class="tab-pane fade" id="tab-kyc">
        <div class="lms-card mb-4">
            <div class="lms-card-header d-flex justify-content-between">
                <h5 class="lms-card-title"><i class="bi bi-shield-check text-success"></i>KYC Verification</h5>
                <button class="btn-lms-secondary py-1 px-2" style="font-size:12px" data-bs-toggle="modal" data-bs-target="#addKycModal"><i class="bi bi-plus"></i>Add KYC Record</button>
            </div>
            <div class="lms-table-wrapper" style="border:none">
                <table class="lms-table">
                    <thead>
                        <tr><th>Document Type</th><th>Category</th><th>Document No.</th><th>Issue / Expiry</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($customer->kyc as $kyc)
                        <tr>
                            <td class="fw-semibold text-white">{{ ucfirst(str_replace('_',' ',$kyc->document_type)) }}</td>
                            <td>{{ ucfirst($kyc->document_category) }}</td>
                            <td>{{ $kyc->document_number ?? '—' }}</td>
                            <td>{{ $kyc->issue_date ? $kyc->issue_date->format('d M Y') : '—' }} / {{ $kyc->expiry_date ? $kyc->expiry_date->format('d M Y') : '—' }}</td>
                            <td><span class="badge-status badge-{{ $kyc->status==='verified'?'success':($kyc->status==='rejected'?'danger':'warning') }}">{{ ucfirst($kyc->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No KYC records uploaded</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- EMPLOYMENT & BANK TAB --}}
    <div class="tab-pane fade" id="tab-employment">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="lms-card h-100">
                    <div class="lms-card-header d-flex justify-content-between">
                        <h5 class="lms-card-title"><i class="bi bi-briefcase text-info"></i>Employment Details</h5>
                        <button class="btn-lms-secondary py-1 px-2" style="font-size:12px" data-bs-toggle="modal" data-bs-target="#addEmploymentModal"><i class="bi bi-plus"></i>Add Employment</button>
                    </div>
                    <div class="lms-card-body">
                        @forelse($customer->employment as $emp)
                        <div class="p-3 mb-3 rounded" style="background:var(--bg-card-hover); border:1px solid var(--border)">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-white">{{ $emp->employer_name ?? 'Self Employed' }}</span>
                                <span class="badge bg-info text-uppercase" style="font-size:10px">{{ $emp->employment_type }}</span>
                            </div>
                            <div class="text-muted" style="font-size:12px">Designation: {{ $emp->designation ?? '—' }}</div>
                            <div class="text-success fw-semibold mt-1" style="font-size:13px">Monthly Income: {{ config('lms.currency_symbol') }}{{ number_format($emp->monthly_income, 2) }}</div>
                        </div>
                        @empty
                        <div class="text-muted text-center py-4">No employment records</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="lms-card h-100">
                    <div class="lms-card-header d-flex justify-content-between">
                        <h5 class="lms-card-title"><i class="bi bi-bank text-warning"></i>Bank Accounts</h5>
                        <button class="btn-lms-secondary py-1 px-2" style="font-size:12px" data-bs-toggle="modal" data-bs-target="#addBankModal"><i class="bi bi-plus"></i>Add Bank Account</button>
                    </div>
                    <div class="lms-card-body">
                        @forelse($customer->bankAccounts as $bank)
                        <div class="p-3 mb-3 rounded" style="background:var(--bg-card-hover); border:1px solid var(--border)">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold text-white">{{ $bank->bank_name }}</span>
                                @if($bank->is_primary)<span class="badge bg-success" style="font-size:10px">Primary</span>@endif
                            </div>
                            <div class="text-secondary" style="font-size:13px">A/C: {{ $bank->account_number }} ({{ strtoupper($bank->account_type) }})</div>
                            <div class="text-muted" style="font-size:12px">IFSC: {{ $bank->ifsc_code ?? '—' }} | Holder: {{ $bank->account_holder_name }}</div>
                        </div>
                        @empty
                        <div class="text-muted text-center py-4">No bank accounts added</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- NOMINEES & REFERENCES TAB --}}
    <div class="tab-pane fade" id="tab-nominees">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="lms-card h-100">
                    <div class="lms-card-header d-flex justify-content-between">
                        <h5 class="lms-card-title"><i class="bi bi-people text-primary"></i>Nominees</h5>
                        <button class="btn-lms-secondary py-1 px-2" style="font-size:12px" data-bs-toggle="modal" data-bs-target="#addNomineeModal"><i class="bi bi-plus"></i>Add Nominee</button>
                    </div>
                    <div class="lms-card-body">
                        @forelse($customer->nominees as $nom)
                        <div class="p-3 mb-2 rounded" style="background:var(--bg-card-hover); border:1px solid var(--border)">
                            <div class="fw-semibold text-white">{{ $nom->name }} ({{ $nom->relationship }})</div>
                            <div class="text-muted" style="font-size:12px">Share: {{ $nom->share_percent }}% | Phone: {{ $nom->phone ?? '—' }}</div>
                        </div>
                        @empty
                        <div class="text-muted text-center py-4">No nominees listed</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="lms-card h-100">
                    <div class="lms-card-header d-flex justify-content-between">
                        <h5 class="lms-card-title"><i class="bi bi-person-lines-fill text-accent"></i>References</h5>
                        <button class="btn-lms-secondary py-1 px-2" style="font-size:12px" data-bs-toggle="modal" data-bs-target="#addReferenceModal"><i class="bi bi-plus"></i>Add Reference</button>
                    </div>
                    <div class="lms-card-body">
                        @forelse($customer->references as $ref)
                        <div class="p-3 mb-2 rounded" style="background:var(--bg-card-hover); border:1px solid var(--border)">
                            <div class="fw-semibold text-white">{{ $ref->name }}</div>
                            <div class="text-muted" style="font-size:12px">Relation: {{ $ref->relationship ?? '—' }} | Phone: {{ $ref->phone }}</div>
                        </div>
                        @empty
                        <div class="text-muted text-center py-4">No references listed</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- LOANS TAB --}}
    <div class="tab-pane fade" id="tab-loans">
        <div class="lms-card">
            <div class="lms-card-header"><h5 class="lms-card-title"><i class="bi bi-cash-stack text-success"></i>Loan History</h5></div>
            <div class="lms-table-wrapper" style="border:none">
                <table class="lms-table">
                    <thead>
                        <tr><th>Loan No.</th><th>Type</th><th>Applied Amount</th><th>Interest Rate</th><th>Tenure</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        @forelse($customer->loans as $loan)
                        <tr data-href="{{ route('loans.show', $loan) }}">
                            <td class="fw-bold text-primary">{{ $loan->loan_no }}</td>
                            <td>{{ ucfirst(str_replace('_',' ',$loan->loan_type)) }}</td>
                            <td class="fw-semibold text-white">{{ config('lms.currency_symbol') }}{{ number_format($loan->applied_amount, 2) }}</td>
                            <td>{{ $loan->interest_rate }}% ({{ ucfirst($loan->interest_type) }})</td>
                            <td>{{ $loan->tenure_months }} Mos</td>
                            <td><span class="badge-status badge-{{ \App\Enums\LoanStatus::from($loan->status)->color() }}">{{ \App\Enums\LoanStatus::from($loan->status)->label() }}</span></td>
                            <td onclick="event.stopPropagation()"><a href="{{ route('loans.show', $loan) }}" class="btn-icon view"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No loans associated with this customer</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODALS FOR SUB-RESOURCES --}}
{{-- Blacklist Modal --}}
<div class="modal fade" id="blacklistModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('customers.blacklist', $customer) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title text-danger"><i class="bi bi-slash-circle me-2"></i>Blacklist Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Blacklist Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" rows="3" required placeholder="Provide clear justification for blacklisting..."></textarea>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-lms-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-lms-danger">Confirm Blacklist</button></div>
        </form>
    </div>
</div>

{{-- Add Address Modal --}}
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('customers.addresses.store', $customer) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Address</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="current">Current / Residential</option>
                            <option value="permanent">Permanent</option>
                            <option value="office">Office</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ownership</label>
                        <select name="ownership" class="form-select"><option value="owned">Owned</option><option value="rented">Rented</option><option value="parental">Parental</option></select>
                    </div>
                    <div class="col-12"><label class="form-label">Address Line 1</label><input type="text" name="address_line1" class="form-control" required></div>
                    <div class="col-12"><label class="form-label">Address Line 2</label><input type="text" name="address_line2" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">City</label><input type="text" name="city" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">State</label><input type="text" name="state" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Pincode</label><input type="text" name="pin_code" class="form-control" required></div>
                    <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_primary" value="1" id="primaryCheck"><label class="form-check-label" for="primaryCheck">Set as primary address</label></div></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-lms-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-lms-primary">Save Address</button></div>
        </form>
    </div>
</div>

{{-- Add KYC Modal --}}
<div class="modal fade" id="addKycModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('customers.kyc.store', $customer) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add KYC Document</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Document Type</label>
                        <select name="document_type" class="form-select" required>
                            <option value="pan">PAN Card</option>
                            <option value="aadhaar">Aadhaar Card</option>
                            <option value="passport">Passport</option>
                            <option value="voter_id">Voter ID</option>
                            <option value="driving_license">Driving License</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="document_category" class="form-select" required>
                            <option value="identity">Identity Proof</option>
                            <option value="address">Address Proof</option>
                            <option value="income">Income Proof</option>
                        </select>
                    </div>
                    <div class="col-12"><label class="form-label">Document Number</label><input type="text" name="document_number" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Issue Date</label><input type="date" name="issue_date" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Expiry Date</label><input type="date" name="expiry_date" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-lms-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-lms-primary">Save KYC</button></div>
        </form>
    </div>
</div>

{{-- Add Employment Modal --}}
<div class="modal fade" id="addEmploymentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('customers.employment.store', $customer) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Employment Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Type</label>
                        <select name="employment_type" class="form-select" required>
                            <option value="salaried">Salaried</option>
                            <option value="self_employed">Self Employed</option>
                            <option value="business">Business Owner</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Employer Name</label><input type="text" name="employer_name" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Designation</label><input type="text" name="designation" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Monthly Income (₹)</label><input type="number" step="0.01" name="monthly_income" class="form-control" required></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-lms-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-lms-primary">Save Employment</button></div>
        </form>
    </div>
</div>

{{-- Add Bank Account Modal --}}
<div class="modal fade" id="addBankModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('customers.bank-accounts.store', $customer) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Bank Account</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Bank Name</label><input type="text" name="bank_name" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Account Number</label><input type="text" name="account_number" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Holder Name</label><input type="text" name="account_holder_name" class="form-control" value="{{ $customer->full_name }}" required></div>
                    <div class="col-md-6">
                        <label class="form-label">Account Type</label>
                        <select name="account_type" class="form-select" required><option value="savings">Savings</option><option value="current">Current</option></select>
                    </div>
                    <div class="col-md-6"><label class="form-label">IFSC Code</label><input type="text" name="ifsc_code" class="form-control text-uppercase" maxlength="11"></div>
                    <div class="col-md-6"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" name="is_primary" value="1" id="bankPrimary"><label class="form-check-label" for="bankPrimary">Primary Account</label></div></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-lms-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-lms-primary">Save Bank Account</button></div>
        </form>
    </div>
</div>

{{-- Add Nominee Modal --}}
<div class="modal fade" id="addNomineeModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('customers.nominees.store', $customer) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Nominee</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Relationship</label><input type="text" name="relationship" class="form-control" required placeholder="Spouse, Father, Son..."></div>
                    <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Share Percent (%)</label><input type="number" step="0.01" name="share_percent" class="form-control" value="100" required></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-lms-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-lms-primary">Save Nominee</button></div>
        </form>
    </div>
</div>

{{-- Add Reference Modal --}}
<div class="modal fade" id="addReferenceModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('customers.references.store', $customer) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Reference</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Phone Number</label><input type="text" name="phone" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Relationship</label><input type="text" name="relationship" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Occupation</label><input type="text" name="occupation" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-lms-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-lms-primary">Save Reference</button></div>
        </form>
    </div>
</div>
@endsection
