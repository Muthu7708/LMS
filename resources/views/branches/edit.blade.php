@extends('layouts.app')
@section('title', 'Edit Branch')
@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Branch</h1>
</div>
<form method="POST" action="{{ route('branches.update', $branch) }}">
    @csrf
    @method('PUT')
    <input type="text" name="name" value="{{ $branch->name }}" required>
    <input type="text" name="code" value="{{ $branch->code }}" required>
    <button type="submit">Update</button>
</form>
@endsection
