@extends('layouts.app')
@section('title', 'Add New Branch')
@section('content')
<div class="page-header">
    <h1 class="page-title">Add New Branch</h1>
</div>
<form method="POST" action="{{ route('branches.store') }}">
    @csrf
    <input type="text" name="name" required>
    <input type="text" name="code" required>
    <button type="submit">Save</button>
</form>
@endsection
