@extends('layouts.app')
@section('title', 'Branches')
@section('breadcrumb')
<li class="breadcrumb-item active">Branches</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-building me-2 text-primary"></i>Branch Management</h1>
        <p class="page-subtitle">Manage organization branches and regional offices</p>
    </div>
    @can('branch.create')
    <button class="btn-lms-primary" data-bs-toggle="modal" data-bs-target="#createBranchModal"><i class="bi bi-plus-lg"></i>Add Branch</button>
    @endcan
</div>

<div class="row g-4 fade-in-up">
    @foreach($branches as $b)
    <div class="col-md-6 col-lg-4">
        <div class="lms-card h-100">
            <div class="lms-card-header d-flex justify-content-between">
                <h5 class="lms-card-title"><i class="bi bi-building text-info"></i>{{ $b->name }}</h5>
                @if($b->is_head_office)
                <span class="badge bg-primary">Head Office</span>
                @endif
            </div>
            <div class="lms-card-body">
                <div class="text-muted small mb-2"><i class="bi bi-geo-alt me-1"></i>{{ $b->address ?? 'No address set' }}, {{ $b->city }}</div>
                <div class="text-muted small mb-3"><i class="bi bi-telephone me-1"></i>{{ $b->phone ?? '—' }} | <i class="bi bi-envelope me-1"></i>{{ $b->email ?? '—' }}</div>

                <div class="row g-2 text-center pt-3 border-top border-secondary">
                    <div class="col-6">
                        <div class="fw-bold text-white fs-5">{{ $b->users_count }}</div>
                        <div class="text-muted" style="font-size:11px">Staff Members</div>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold text-success fs-5">{{ $b->loans_count }}</div>
                        <div class="text-muted" style="font-size:11px">Total Loans</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Add Branch Modal --}}
<div class="modal fade" id="createBranchModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('branches.store') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add New Branch</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Branch Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Branch Code</label><input type="text" name="code" class="form-control text-uppercase" required placeholder="e.g. BR-DELHI"></div>
                    <div class="col-12"><label class="form-label">Address</label><input type="text" name="address" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">City</label><input type="text" name="city" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">State</label><input type="text" name="state" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-lms-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-lms-primary">Save Branch</button></div>
        </form>
    </div>
</div>
@endsection
