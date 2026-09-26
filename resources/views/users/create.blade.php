@extends('layouts.app')
@section('title', 'Add User')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Add New System User</h1>
        <p class="page-subtitle">Create a user account with role permissions and branch assignment</p>
    </div>
    <a href="{{ route('users.index') }}" class="btn-lms-secondary"><i class="bi bi-arrow-left"></i>Back to Users</a>
</div>

<form method="POST" action="{{ route('users.store') }}" class="fade-in-up">
    @csrf
    <div class="lms-card" style="max-width:700px">
        <div class="lms-card-header"><h5 class="lms-card-title"><i class="bi bi-shield-lock text-primary"></i>User Account Details</h5></div>
        <div class="lms-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Branch Assignment <span class="text-danger">*</span></label>
                    <select name="branch_id" class="form-select" required>
                        @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="role" class="form-select text-capitalize" required>
                        @foreach($roles as $r)
                        <option value="{{ $r->name }}">{{ ucfirst(str_replace('_',' ',$r->name)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-6">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('users.index') }}" class="btn-lms-secondary">Cancel</a>
                <button type="submit" class="btn-lms-primary"><i class="bi bi-check-circle"></i>Create User Account</button>
            </div>
        </div>
    </div>
</form>
@endsection
