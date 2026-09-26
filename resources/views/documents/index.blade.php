@extends('layouts.app')
@section('title', 'Documents')
@section('breadcrumb')
<li class="breadcrumb-item active">Documents</li>
@endsection

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title"><i class="bi bi-folder2-open me-2 text-primary"></i>Document Management</h1>
        <p class="page-subtitle">Centralized document repository with automated versioning and review workflows</p>
    </div>
</div>

<div class="lms-card fade-in-up">
    <div class="lms-table-wrapper" style="border:none">
        <table class="lms-table">
            <thead>
                <tr><th>Title</th><th>Category</th><th>Entity</th><th>Version</th><th>Status</th><th>Uploaded By</th><th>Action</th></tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                <tr>
                    <td class="fw-bold text-white">{{ $doc->title }}</td>
                    <td>{{ ucfirst($doc->document_type) }}</td>
                    <td><span class="badge bg-dark">{{ class_basename($doc->documentable_type) }} #{{ $doc->documentable_id }}</span></td>
                    <td><span class="badge bg-primary">v{{ $doc->currentVersion->version_number ?? 1 }}</span></td>
                    <td><span class="badge-status badge-{{ $doc->status==='approved'?'success':($doc->status==='rejected'?'danger':'warning') }}">{{ ucfirst($doc->status) }}</span></td>
                    <td>{{ $doc->uploadedBy->name ?? 'System' }}</td>
                    <td>
                        @if($doc->currentVersion)
                        <a href="{{ route('documents.download', $doc->currentVersion) }}" class="btn-icon view" data-bs-toggle="tooltip" title="Download"><i class="bi bi-download"></i></a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No documents uploaded yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
