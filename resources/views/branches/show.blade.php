@extends('layouts.app')
@section('title', 'Branch Details — ' . $branch->name)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('branches.index') }}">Branches</a></li>
<li class="breadcrumb-item active">{{ $branch->name }}</li>
@endsection
@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-building me-2 text-primary"></i>{{ $branch->name }} ({{ $branch->code }})</h1>
        <p class="page-subtitle">{{ $branch->city }}, {{ $branch->state }}</p>
    </div>
    <a href="{{ route('branches.index') }}" class="btn-lms-secondary"><i class="bi bi-arrow-left"></i> Back to Branches</a>
</div>
<div class="lms-card">
    <div class="lms-card-body">
        <p><strong>Code:</strong> {{ $branch->code }}</p>
        <p><strong>Email:</strong> {{ $branch->email ?? 'N/A' }}</p>
        <p><strong>Phone:</strong> {{ $branch->phone ?? 'N/A' }}</p>
    </div>
</div>
@endsection
