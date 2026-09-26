@extends('layouts.app')
@section('title', 'Notifications')
@section('breadcrumb')
<li class="breadcrumb-item active">Notifications</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-bell-fill me-2 text-primary"></i>System Notifications</h1>
        <p class="page-subtitle">Alerts, payment due reminders, and workflow notifications</p>
    </div>
</div>

<div class="lms-card fade-in-up">
    <div class="empty-state py-5">
        <i class="bi bi-bell-slash text-muted" style="font-size:48px"></i>
        <h6 class="mt-3 text-white">No new notifications</h6>
        <p class="text-muted">You are all caught up!</p>
    </div>
</div>
@endsection
