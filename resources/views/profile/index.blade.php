@extends('layouts.app')
@section('title', 'My Profile')
@section('breadcrumb')
<li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-person-circle me-2 text-primary"></i>My Account Profile</h1>
        <p class="page-subtitle">Manage personal info, phone number, and security password</p>
    </div>
</div>

<form method="POST" action="{{ route('profile.update') }}" class="fade-in-up" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="lms-card" style="max-width:700px">
        <div class="lms-card-header"><h5 class="lms-card-title"><i class="bi bi-person text-primary"></i>Personal & Security Info</h5></div>
        <div class="lms-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address (Read Only)</label>
                    <input type="email" class="form-control text-muted" value="{{ $user->email }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-control text-muted text-uppercase" value="{{ $user->getRoleNames()->first() ?? 'User' }}" readonly>
                </div>

                <hr class="border-secondary opacity-25 my-4">
                <h6 class="text-white fw-bold">Change Password</h6>

                <div class="col-md-12">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="submit" class="btn-lms-primary"><i class="bi bi-check-circle"></i>Update Profile</button>
            </div>
        </div>
    </div>
</form>
@endsection
