@extends('layouts.app')
@section('title', 'Customers')
@section('breadcrumb')
<li class="breadcrumb-item active">Customers</li>
@endsection
@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-people-fill me-2 text-primary"></i>Customer Management</h1>
        <p class="page-subtitle">Manage all customer profiles, KYC, and loan history</p>
    </div>
    @can('customer.create')
    <a href="{{ route('customers.create') }}" class="btn-lms-primary"><i class="bi bi-plus-lg"></i>Add Customer</a>
    @endcan
</div>

<div class="lms-card fade-in-up">
    <div class="lms-card-header">
        <div class="filter-row" style="margin-bottom:0; flex:1">
            <form method="GET" class="d-flex gap-2 flex-wrap" style="flex:1">
                <div class="search-box" style="flex:1; min-width:200px">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control" name="search" placeholder="Search by name, mobile, PAN, customer no..." value="{{ request('search') }}">
                </div>
                <select class="form-select" name="status" style="width:150px">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
                    <option value="blacklisted" {{ request('status')=='blacklisted'?'selected':'' }}>Blacklisted</option>
                    <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactive</option>
                </select>
                <button type="submit" class="btn-lms-primary"><i class="bi bi-funnel"></i>Filter</button>
                @if(request()->hasAny(['search','status']))
                <a href="{{ route('customers.index') }}" class="btn-lms-secondary"><i class="bi bi-x"></i>Clear</a>
                @endif
            </form>
        </div>
    </div>
    <div class="lms-table-wrapper" style="border:none; border-radius:0">
        <table class="lms-table">
            <thead>
                <tr>
                    <th>#</th><th>Customer No.</th><th>Name</th><th>Mobile</th>
                    <th>PAN</th><th>Branch</th><th>Active Loans</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                <tr data-href="{{ route('customers.show', $c) }}">
                    <td>{{ $loop->iteration + ($customers->firstItem() - 1) }}</td>
                    <td><span style="color:var(--primary-light);font-weight:600">{{ $c->customer_no }}</span></td>
                    <td>
                        <div style="font-weight:600;color:var(--text-primary)">{{ $c->full_name }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $c->email }}</div>
                    </td>
                    <td>{{ $c->mobile }}</td>
                    <td>{{ $c->pan ?? '—' }}</td>
                    <td>{{ $c->branch->name ?? '—' }}</td>
                    <td>
                        <span class="badge-status badge-{{ $c->activeLoans->count() > 0 ? 'success' : 'secondary' }}">
                            {{ $c->activeLoans->count() }}
                        </span>
                    </td>
                    <td>
                        @if($c->status === 'blacklisted')
                            <span class="badge-status badge-danger">Blacklisted</span>
                        @elseif($c->status === 'active')
                            <span class="badge-status badge-success">Active</span>
                        @else
                            <span class="badge-status badge-secondary">{{ ucfirst($c->status) }}</span>
                        @endif
                    </td>
                    <td onclick="event.stopPropagation()">
                        <div class="d-flex gap-1">
                            <a href="{{ route('customers.show', $c) }}" class="btn-icon view" data-bs-toggle="tooltip" title="View"><i class="bi bi-eye"></i></a>
                            @can('customer.edit')
                            <a href="{{ route('customers.edit', $c) }}" class="btn-icon edit" data-bs-toggle="tooltip" title="Edit"><i class="bi bi-pencil"></i></a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9"><div class="empty-state"><i class="bi bi-people"></i><h6>No customers found</h6></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center p-3">
        <span style="font-size:13px;color:var(--text-muted)">Showing {{ $customers->firstItem() }}–{{ $customers->lastItem() }} of {{ $customers->total() }} customers</span>
        {{ $customers->withQueryString()->links('partials.pagination') }}
    </div>
</div>
@endsection
