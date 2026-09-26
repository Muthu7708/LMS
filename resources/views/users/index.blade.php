@extends('layouts.app')
@section('title', 'Users & Roles')
@section('breadcrumb')
<li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-person-badge-fill me-2 text-primary"></i>User & Role Management</h1>
        <p class="page-subtitle">Manage organization staff, branch assignments, and security roles</p>
    </div>
    @can('user.create')
    <a href="{{ route('users.create') }}" class="btn-lms-primary"><i class="bi bi-person-plus"></i>Add New User</a>
    @endcan
</div>

<div class="lms-card fade-in-up">
    <div class="lms-card-header">
        <form method="GET" class="d-flex gap-2 flex-wrap" style="flex:1">
            <div class="search-box" style="flex:1">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" name="search" placeholder="Search by name, email..." value="{{ request('search') }}">
            </div>
            <select class="form-select" name="branch_id" style="width:180px">
                <option value="">All Branches</option>
                @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-lms-primary"><i class="bi bi-funnel"></i>Filter</button>
        </form>
    </div>
    <div class="lms-table-wrapper" style="border:none">
        <table class="lms-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Branch</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $u->avatar_url ?? asset('images/default-avatar.png') }}" style="width:32px;height:32px;border-radius:50%">
                            <div>
                                <div class="fw-bold text-white">{{ $u->name }}</div>
                                <div style="font-size:11px;color:var(--text-muted)">{{ $u->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $u->branch->name ?? 'All Branches' }}</td>
                    <td>
                        <span class="badge bg-primary text-uppercase" style="font-size:10px">
                            {{ $u->getRoleNames()->first() ?? 'User' }}
                        </span>
                    </td>
                    <td>
                        @if($u->is_active)
                            <span class="badge-status badge-success">Active</span>
                        @else
                            <span class="badge-status badge-danger">Inactive</span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Never' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            @can('user.edit')
                            <a href="{{ route('users.edit', $u) }}" class="btn-icon edit" data-bs-toggle="tooltip" title="Edit User"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('users.toggle-active', $u) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-icon {{ $u->is_active ? 'delete' : 'view' }}" data-bs-toggle="tooltip" title="{{ $u->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="bi bi-power"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center p-3">
        <span style="font-size:13px;color:var(--text-muted)">Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users</span>
        {{ $users->withQueryString()->links('partials.pagination') }}
    </div>
</div>
@endsection
