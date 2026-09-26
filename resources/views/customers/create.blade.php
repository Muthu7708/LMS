@extends('layouts.app')
@section('title', 'Add Customer')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Add New Customer</h1>
        <p class="page-subtitle">Create a new customer profile with personal, contact, and address details</p>
    </div>
    <a href="{{ route('customers.index') }}" class="btn-lms-secondary"><i class="bi bi-arrow-left"></i>Back to List</a>
</div>

<form method="POST" action="{{ route('customers.store') }}" class="fade-in-up">
    @csrf
    <div class="row g-4">
        {{-- Basic Information --}}
        <div class="col-lg-8">
            <div class="lms-card mb-4">
                <div class="lms-card-header">
                    <h5 class="lms-card-title"><i class="bi bi-person text-primary"></i>Personal Information</h5>
                </div>
                <div class="lms-card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control @error('middle_name') is-invalid @enderror" value="{{ old('middle_name') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                            <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender')=='male'?'selected':'' }}>Male</option>
                                <option value="female" {{ old('gender')=='female'?'selected':'' }}>Female</option>
                                <option value="other" {{ old('gender')=='other'?'selected':'' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">PAN Number</label>
                            <input type="text" name="pan" class="form-control text-uppercase @error('pan') is-invalid @enderror" value="{{ old('pan') }}" placeholder="ABCDE1234F" maxlength="10">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Aadhaar Number</label>
                            <input type="text" name="aadhaar" class="form-control @error('aadhaar') is-invalid @enderror" value="{{ old('aadhaar') }}" placeholder="12 Digit Aadhaar" maxlength="12">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Marital Status</label>
                            <select name="marital_status" class="form-select">
                                <option value="">Select Status</option>
                                <option value="single" {{ old('marital_status')=='single'?'selected':'' }}>Single</option>
                                <option value="married" {{ old('marital_status')=='married'?'selected':'' }}>Married</option>
                                <option value="divorced" {{ old('marital_status')=='divorced'?'selected':'' }}>Divorced</option>
                                <option value="widowed" {{ old('marital_status')=='widowed'?'selected':'' }}>Widowed</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Primary Address --}}
            <div class="lms-card">
                <div class="lms-card-header">
                    <h5 class="lms-card-title"><i class="bi bi-geo-alt text-accent"></i>Primary Address</h5>
                </div>
                <div class="lms-card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Address Type <span class="text-danger">*</span></label>
                            <select name="address_type" class="form-select @error('address_type') is-invalid @enderror" required>
                                <option value="current" {{ old('address_type')=='current'?'selected':'' }}>Current / Residential</option>
                                <option value="permanent" {{ old('address_type')=='permanent'?'selected':'' }}>Permanent</option>
                                <option value="office" {{ old('address_type')=='office'?'selected':'' }}>Office</option>
                                <option value="other" {{ old('address_type')=='other'?'selected':'' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                            <input type="text" name="address_line1" class="form-control @error('address_line1') is-invalid @enderror" value="{{ old('address_line1') }}" required placeholder="House/Flat No., Building Name, Street">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="address_line2" class="form-control" value="{{ old('address_line2') }}" placeholder="Landmark, Area">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State <span class="text-danger">*</span></label>
                            <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" value="{{ old('state') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pincode <span class="text-danger">*</span></label>
                            <input type="text" name="pin_code" class="form-control @error('pin_code') is-invalid @enderror" value="{{ old('pin_code') }}" required maxlength="10">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Organization & Submit --}}
        <div class="col-lg-4">
            <div class="lms-card mb-4">
                <div class="lms-card-header">
                    <h5 class="lms-card-title"><i class="bi bi-building text-info"></i>Assignment</h5>
                </div>
                <div class="lms-card-body">
                    <div class="mb-3">
                        <label class="form-label">Branch <span class="text-danger">*</span></label>
                        <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                            <option value="">Select Branch</option>
                            @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id', auth()->user()->branch_id) == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->code }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <hr class="border-secondary opacity-25 my-4">
                    <button type="submit" class="btn-lms-primary w-100 justify-content-center">
                        <i class="bi bi-check-circle-fill"></i>Save Customer Profile
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
