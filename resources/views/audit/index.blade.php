@extends('layouts.app')
@section('title', 'Audit Logs')
@section('breadcrumb')
<li class="breadcrumb-item active">Audit Logs</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-shield-check me-2 text-primary"></i>Security & System Audit Logs</h1>
        <p class="page-subtitle">Complete audit trail of user activities, approvals, disbursements, and system events</p>
    </div>
</div>

<div class="lms-card fade-in-up">
    <div class="lms-table-wrapper" style="border:none">
        <table class="lms-table">
            <thead>
                <tr><th>Timestamp</th><th>User</th><th>Role</th><th>Event / Action</th><th>Entity</th><th>IP Address</th></tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at ? $log->created_at->format('d M Y, h:i:s A') : '—' }}</td>
                    <td class="fw-bold text-white">{{ $log->user_name ?? 'System' }}</td>
                    <td><span class="badge bg-primary text-uppercase" style="font-size:10px">{{ $log->user_role ?? 'User' }}</span></td>
                    <td class="fw-semibold text-info">{{ ucfirst($log->event) }}</td>
                    <td>{{ $log->model_type }} #{{ $log->model_id }}</td>
                    <td class="font-monospace small text-muted">{{ $log->ip_address ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No audit logs recorded yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center p-3">
        <span style="font-size:13px;color:var(--text-muted)">Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }} logs</span>
        {{ $logs->withQueryString()->links('partials.pagination') }}
    </div>
</div>
@endsection
