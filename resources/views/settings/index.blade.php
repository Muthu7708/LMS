@extends('layouts.app')
@section('title', 'System Settings')
@section('breadcrumb')
<li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-gear-fill me-2 text-primary"></i>System & Business Settings</h1>
        <p class="page-subtitle">Configure organization rules, penalty interest rates, and loan default constants</p>
    </div>
</div>

<form method="POST" action="{{ route('settings.update') }}" class="fade-in-up">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="lms-card mb-4">
                <div class="lms-card-header"><h5 class="lms-card-title"><i class="bi bi-building text-primary"></i>Company Information</h5></div>
                <div class="lms-card-body">
                    <div class="mb-3">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="{{ config('lms.company_name') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Currency Symbol</label>
                        <input type="text" name="currency_symbol" class="form-control" value="{{ config('lms.currency_symbol') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="lms-card mb-4">
                <div class="lms-card-header"><h5 class="lms-card-title"><i class="bi bi-calculator text-warning"></i>Loan Rules & Penalties</h5></div>
                <div class="lms-card-body">
                    <div class="mb-3">
                        <label class="form-label">Late Payment Penalty Rate (% per day)</label>
                        <input type="number" step="0.01" name="penalty_daily_rate" class="form-control" value="0.05">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Grace Period (Days)</label>
                        <input type="number" name="grace_period_days" class="form-control" value="3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">NPA Classification Threshold (Days)</label>
                        <input type="number" name="npa_days_threshold" class="form-control" value="90">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn-lms-primary"><i class="bi bi-check-circle"></i>Save System Settings</button>
        </div>
    </div>
</form>
@endsection
