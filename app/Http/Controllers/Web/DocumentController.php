<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = Document::where('company_id', Auth::user()->company_id)
            ->with(['uploadedBy', 'currentVersion', 'documentable'])
            ->latest()
            ->paginate(20);

        return view('documents.index', compact('documents'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'documentable_type' => 'required|string',
            'documentable_id'   => 'required|integer',
            'title'             => 'required|string|max:200',
            'document_type'     => 'required|string',
            'file'              => 'required|file|max:10240', // Max 10MB
        ]);

        $file = $request->file('file');
        $path = $file->store('documents/' . date('Y/m'), 'public');

        $doc = Document::create([
            'company_id'       => Auth::user()->company_id,
            'documentable_type'=> $request->documentable_type,
            'documentable_id'  => $request->documentable_id,
            'uploaded_by'      => Auth::id(),
            'document_type'    => $request->document_type,
            'title'            => $request->title,
            'status'           => 'pending_review',
        ]);

        DocumentVersion::create([
            'document_id'   => $doc->id,
            'uploaded_by'   => Auth::id(),
            'version_number'=> 1,
            'file_path'     => $path,
            'file_name'     => $file->getClientOriginalName(),
            'file_type'     => $file->getClientMimeType(),
            'file_size'     => $file->getSize(),
            'is_current'    => true,
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function show(Document $document)
    {
        $document->load(['versions.uploadedBy', 'uploadedBy', 'reviewedBy']);
        return view('documents.show', compact('document'));
    }

    public function addVersion(Request $request, Document $document)
    {
        $request->validate(['file' => 'required|file|max:10240']);

        $file = $request->file('file');
        $path = $file->store('documents/' . date('Y/m'), 'public');
        $nextVer = $document->versions()->max('version_number') + 1;

        $document->versions()->update(['is_current' => false]);

        DocumentVersion::create([
            'document_id'   => $document->id,
            'uploaded_by'   => Auth::id(),
            'version_number'=> $nextVer,
            'file_path'     => $path,
            'file_name'     => $file->getClientOriginalName(),
            'file_type'     => $file->getClientMimeType(),
            'file_size'     => $file->getSize(),
            'is_current'    => true,
            'change_notes'  => $request->change_notes,
        ]);

        return back()->with('success', "Version {$nextVer} uploaded.");
    }

    public function approve(Request $request, Document $document)
    {
        $document->update([
            'status'         => 'approved',
            'is_verified'    => true,
            'reviewed_by'    => Auth::id(),
            'reviewed_at'    => now(),
            'review_remarks' => $request->remarks,
        ]);

        return back()->with('success', 'Document approved.');
    }

    public function download(DocumentVersion $version)
    {
        return Storage::disk('public')->download($version->file_path, $version->file_name);
    }
}
