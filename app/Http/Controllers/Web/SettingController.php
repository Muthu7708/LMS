<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::where('company_id', Auth::user()->company_id)->get()->groupBy('group');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $companyId = Auth::user()->company_id;

        foreach ($request->except('_token', '_method') as $key => $value) {
            Setting::updateOrCreate(
                ['company_id' => $companyId, 'key' => $key],
                ['value' => is_array($value) ? json_encode($value) : $value]
            );
        }

        return back()->with('success', 'System settings updated successfully.');
    }
}
