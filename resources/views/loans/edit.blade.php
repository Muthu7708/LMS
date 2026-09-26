@extends('layouts.app')
@section('title', 'Edit Loan Application')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('loans.index') }}">Loans</a></li>
<li class="breadcrumb-item"><a href="{{ route('loans.show', $loan) }}">{{ $loan->loan_no }}</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Loan Application ({{ $loan->loan_no }})</h1>
        <p class="page-subtitle">Update loan application details, interest rate, tenure, and repayment terms</p>
    </div>
    <a href="{{ route('loans.show', $loan) }}" class="btn-lms-secondary"><i class="bi bi-arrow-left"></i>Back to Loan</a>
</div>

<form method="POST" action="{{ route('loans.update', $loan) }}" class="fade-in-up">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="lms-card mb-4">
                <div class="lms-card-header">
                    <h5 class="lms-card-title"><i class="bi bi-person text-primary"></i>Borrower & Loan Type</h5>
                </div>
                <div class="lms-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Select Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', $loan->customer_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->full_name }} ({{ $c->customer_no }}) — {{ $c->mobile }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Branch <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id', $loan->branch_id) == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }} ({{ $b->code }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Loan Product Category <span class="text-danger">*</span></label>
                            <select name="loan_type" class="form-select @error('loan_type') is-invalid @enderror" required>
                                <option value="personal" {{ old('loan_type', $loan->loan_type)=='personal'?'selected':'' }}>Personal Loan</option>
                                <option value="business" {{ old('loan_type', $loan->loan_type)=='business'?'selected':'' }}>Business Loan</option>
                                <option value="vehicle" {{ old('loan_type', $loan->loan_type)=='vehicle'?'selected':'' }}>Vehicle Loan</option>
                                <option value="mortgage" {{ old('loan_type', $loan->loan_type)=='mortgage'?'selected':'' }}>Mortgage Loan</option>
                                <option value="gold" {{ old('loan_type', $loan->loan_type)=='gold'?'selected':'' }}>Gold Loan</option>
                                <option value="microfinance" {{ old('loan_type', $loan->loan_type)=='microfinance'?'selected':'' }}>Microfinance Loan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">First EMI Date <span class="text-danger">*</span></label>
                            <input type="date" name="first_emi_date" class="form-control @error('first_emi_date') is-invalid @enderror" value="{{ old('first_emi_date', optional($loan->first_emi_date)->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lms-card mb-4">
                <div class="lms-card-header">
                    <h5 class="lms-card-title"><i class="bi bi-calculator text-success"></i>Financial Parameters</h5>
                </div>
                <div class="lms-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Applied Loan Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" id="sim_principal" name="applied_amount" class="form-control @error('applied_amount') is-invalid @enderror" value="{{ old('applied_amount', $loan->applied_amount) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Annual Interest Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" id="sim_rate" name="interest_rate" class="form-control @error('interest_rate') is-invalid @enderror" value="{{ old('interest_rate', $loan->interest_rate) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tenure (Months) <span class="text-danger">*</span></label>
                            <input type="number" id="sim_tenure" name="tenure_months" class="form-control @error('tenure_months') is-invalid @enderror" value="{{ old('tenure_months', $loan->tenure_months) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Interest Calculation Method <span class="text-danger">*</span></label>
                            <select id="sim_method" name="interest_type" class="form-select @error('interest_type') is-invalid @enderror" required>
                                <option value="reducing" {{ old('interest_type', $loan->interest_method)=='reducing'?'selected':'' }}>Reducing Balance (Standard)</option>
                                <option value="flat" {{ old('interest_type', $loan->interest_method)=='flat'?'selected':'' }}>Flat Rate</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Repayment Frequency</label>
                            <select name="repayment_frequency" class="form-select">
                                <option value="monthly" {{ old('repayment_frequency', $loan->payment_frequency)=='monthly'?'selected':'' }}>Monthly</option>
                                <option value="weekly" {{ old('repayment_frequency', $loan->payment_frequency)=='weekly'?'selected':'' }}>Weekly</option>
                                <option value="biweekly" {{ old('repayment_frequency', $loan->payment_frequency)=='biweekly'?'selected':'' }}>Bi-Weekly</option>
                                <option value="bullet" {{ old('repayment_frequency', $loan->payment_frequency)=='bullet'?'selected':'' }}>Bullet Payment</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Processing Fee (₹)</label>
                            <input type="number" step="0.01" name="processing_fee_rate" class="form-control" value="{{ old('processing_fee_rate', $loan->processing_fee) }}">
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn-lms-secondary btn-sm" onclick="simulateEmi()"><i class="bi bi-play-circle me-1"></i>Simulate EMI Schedule</button>
                    </div>

                    <div id="emi_result" class="mt-4 d-none"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="lms-card mb-4">
                <div class="lms-card-header">
                    <h5 class="lms-card-title"><i class="bi bi-card-text text-accent"></i>Additional Details</h5>
                </div>
                <div class="lms-card-body">
                    <div class="mb-3">
                        <label class="form-label">Loan Purpose</label>
                        <textarea name="purpose" class="form-control" rows="3">{{ old('purpose', $loan->purpose) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Officer Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $loan->internal_notes) }}</textarea>
                    </div>
                    <hr class="border-secondary opacity-25 my-4">
                    <button type="submit" class="btn-lms-primary w-100 justify-content-center"><i class="bi bi-save me-1"></i>Update Loan Application</button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function simulateEmi() {
    const principal = document.getElementById('sim_principal').value;
    const annual_rate = document.getElementById('sim_rate').value;
    const tenure_months = document.getElementById('sim_tenure').value;
    const method = document.getElementById('sim_method').value;
    const resultDiv = document.getElementById('emi_result');

    if (!principal || !annual_rate || !tenure_months) {
        alert('Please fill in principal amount, rate, and tenure first.');
        return;
    }

    resultDiv.classList.remove('d-none');
    resultDiv.innerHTML = '<div class="text-center p-3"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted small">Calculating schedule...</p></div>';

    fetch("{{ route('loans.simulate') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            principal: parseFloat(principal),
            annual_rate: parseFloat(annual_rate),
            tenure_months: parseInt(tenure_months),
            method: method
        })
    })
    .then(response => {
        if (!response.ok) throw new Error('Simulation failed.');
        return response.json();
    })
    .then(data => {
        let scheduleRows = '';
        if (data.schedule && data.schedule.length > 0) {
            data.schedule.slice(0, 5).forEach(row => {
                scheduleRows += `<tr>
                    <td>${row.emi_number}</td>
                    <td>${row.due_date}</td>
                    <td>₹${parseFloat(row.emi_amount).toFixed(2)}</td>
                    <td>₹${parseFloat(row.principal).toFixed(2)}</td>
                    <td>₹${parseFloat(row.interest).toFixed(2)}</td>
                    <td>₹${parseFloat(row.balance).toFixed(2)}</td>
                </tr>`;
            });
        }

        resultDiv.innerHTML = `
            <div class="p-3 border rounded bg-dark-subtle">
                <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-calculator me-1"></i>Simulation Result Summary</h6>
                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="text-muted small">Monthly EMI</div>
                        <div class="fs-5 fw-bold text-success">₹${parseFloat(data.emi_amount).toFixed(2)}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Total Interest</div>
                        <div class="fs-5 fw-bold text-warning">₹${parseFloat(data.total_interest).toFixed(2)}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Total Payable</div>
                        <div class="fs-5 fw-bold text-info">₹${parseFloat(data.total_payable).toFixed(2)}</div>
                    </div>
                </div>
                ${data.schedule && data.schedule.length > 0 ? `
                <div class="table-responsive">
                    <table class="table table-sm text-center mb-0 small">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Due Date</th>
                                <th>EMI</th>
                                <th>Principal</th>
                                <th>Interest</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>${scheduleRows}</tbody>
                    </table>
                    ${data.schedule.length > 5 ? `<div class="text-muted text-center mt-1" style="font-size:11px">+ ${data.schedule.length - 5} more installments</div>` : ''}
                </div>` : ''}
            </div>
        `;
    })
    .catch(err => {
        resultDiv.innerHTML = `<div class="alert alert-danger py-2 mb-0">${err.message || 'Error simulating EMI schedule.'}</div>`;
    });
}
</script>
@endpush
@endsection
