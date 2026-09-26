@extends('layouts.app')
@section('title', 'Edit Customer — ' . $customer->customer_no)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
<li class="breadcrumb-item"><a href="{{ route('customers.show', $customer) }}">{{ $customer->customer_no }}</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Customer Profile</h1>
        <p class="page-subtitle">Update profile information for {{ $customer->full_name }} ({{ $customer->customer_no }})</p>
    </div>
    <a href="{{ route('customers.show', $customer) }}" class="btn-lms-secondary"><i class="bi bi-arrow-left"></i>Back to Customer</a>
</div>

<form method="POST" action="{{ route('customers.update', $customer) }}" class="fade-in-up">
    @csrf
    @method('PUT')

    <div class="lms-card mb-4">
        <div class="lms-card-header">
            <h5 class="lms-card-title"><i class="bi bi-person text-primary"></i>Personal Details</h5>
        </div>
        <div class="lms-card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $customer->first_name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $customer->middle_name) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $customer->last_name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                    <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile', $customer->mobile) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $customer->email) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender', $customer->gender)=='male'?'selected':'' }}>Male</option>
                        <option value="female" {{ old('gender', $customer->gender)=='female'?'selected':'' }}>Female</option>
                        <option value="other" {{ old('gender', $customer->gender)=='other'?'selected':'' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $customer->date_of_birth ? $customer->date_of_birth->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">PAN Number</label>
                    <input type="text" name="pan" class="form-control text-uppercase" value="{{ old('pan', $customer->pan) }}" maxlength="10">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Aadhaar Number</label>
                    <input type="text" name="aadhaar" class="form-control" value="{{ old('aadhaar', $customer->aadhaar) }}" maxlength="12">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Marital Status</label>
                    <select name="marital_status" class="form-select">
                        <option value="">Select Status</option>
                        <option value="single" {{ old('marital_status', $customer->marital_status)=='single'?'selected':'' }}>Single</option>
                        <option value="married" {{ old('marital_status', $customer->marital_status)=='married'?'selected':'' }}>Married</option>
                        <option value="divorced" {{ old('marital_status', $customer->marital_status)=='divorced'?'selected':'' }}>Divorced</option>
                        <option value="widowed" {{ old('marital_status', $customer->marital_status)=='widowed'?'selected':'' }}>Widowed</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('customers.show', $customer) }}" class="btn-lms-secondary">Cancel</a>
                <button type="submit" class="btn-lms-primary"><i class="bi bi-check-circle"></i>Update Profile</button>
            </div>
        </div>
    </div>
</form>
@endsection
