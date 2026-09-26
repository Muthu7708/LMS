@extends('layouts.app')
@section('title', 'User Details — ' . $user->name)
@section('content')
<div class="page-header">
    <h1 class="page-title">{{ $user->name }}</h1>
</div>
<div class="lms-card">
    <div class="lms-card-body">
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Branch:</strong> {{ $user->branch->name ?? 'N/A' }}</p>
        <p><strong>Roles:</strong> {{ $user->roles->pluck('name')->implode(', ') }}</p>
    </div>
</div>
@endsection
