<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::where('company_id', Auth::user()->company_id)
            ->when($request->search, fn ($q, $s) => $q->where('user_name', 'LIKE', "%{$s}%")->orWhere('event', 'LIKE', "%{$s}%"))
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('audit.index', compact('logs'));
    }
}
