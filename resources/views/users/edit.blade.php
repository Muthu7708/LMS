@extends('layouts.app')
@section('title', 'Edit User — ' . $user->name)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit User Account</h1>
        <p class="page-subtitle">Modify user details, role, and branch assignment for {{ $user->name }}</p>
    </div>
    <a href="{{ route('users.index') }}" class="btn-lms-secondary"><i class="bi bi-arrow-left"></i>Back to Users</a>
</div>

<form method="POST" action="{{ route('users.update', $user) }}" class="fade-in-up">
    @csrf
    @method('PUT')
    <div class="lms-card" style="max-width:700px">
        <div class="lms-card-header"><h5 class="lms-card-title"><i class="bi bi-shield-lock text-primary"></i>User Account Details</h5></div>
        <div class="lms-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Branch Assignment <span class="text-danger">*</span></label>
                    <select name="branch_id" class="form-select" required>
                        @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ old('branch_id', $user->branch_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="role" class="form-select text-capitalize" required>
                        @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ $user->hasRole($r->name) ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$r->name)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-6">
                    <label class="form-label">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('users.index') }}" class="btn-lms-secondary">Cancel</a>
                <button type="submit" class="btn-lms-primary"><i class="bi bi-check-circle"></i>Update User Account</button>
            </div>
        </div>
    </div>
</form>
@endsection
