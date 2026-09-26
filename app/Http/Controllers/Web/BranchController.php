<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::where('company_id', Auth::user()->company_id)->withCount(['users', 'loans'])->get();
        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'code'      => 'required|string|max:20|unique:branches,code',
            'address'   => 'nullable|string',
            'city'      => 'nullable|string|max:100',
            'state'     => 'nullable|string|max:100',
            'pin_code'  => 'nullable|string|max:10',
            'phone'     => 'nullable|string|max:20',
            'email'     => 'nullable|email',
            'is_head_office' => 'boolean',
        ]);

        Branch::create([
            ...$data,
            'company_id' => Auth::user()->company_id,
            'is_active'  => true,
        ]);

        return redirect()->route('branches.index')->with('success', 'Branch created successfully.');
    }

    public function show(Branch $branch)
    {
        $branch->loadCount(['users', 'loans', 'customers']);
        return view('branches.show', compact('branch'));
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'code'      => 'required|string|max:20|unique:branches,code,'.$branch->id,
            'address'   => 'nullable|string',
            'city'      => 'nullable|string|max:100',
            'state'     => 'nullable|string|max:100',
            'pin_code'  => 'nullable|string|max:10',
            'phone'     => 'nullable|string|max:20',
            'email'     => 'nullable|email',
            'is_active' => 'boolean',
        ]);

        $branch->update($data);
        return redirect()->route('branches.index')->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->loans()->exists() || $branch->users()->exists()) {
            return back()->with('error', 'Cannot delete branch with associated loans or users.');
        }
        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'Branch deleted.');
    }
}
