<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with(['branch', 'roles'])
            ->where('company_id', Auth::user()->company_id)
            ->when($request->search, fn ($q, $s) => $q->where('name', 'LIKE', "%{$s}%")->orWhere('email', 'LIKE', "%{$s}%"))
            ->when($request->branch_id, fn ($q, $b) => $q->where('branch_id', $b))
            ->paginate(20)->withQueryString();

        $branches = Branch::active()->get();
        return view('users.index', compact('users', 'branches'));
    }

    public function create()
    {
        $branches = Branch::active()->get();
        $roles    = Role::all();
        return view('users.create', compact('branches', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'phone'     => 'nullable|string|max:20',
            'password'  => 'required|string|min:8|confirmed',
            'branch_id' => 'required|exists:branches,id',
            'role'      => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
            'password'   => Hash::make($data['password']),
            'company_id' => Auth::user()->company_id,
            'branch_id'  => $data['branch_id'],
            'is_active'  => true,
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('users.index')->with('success', "User {$user->name} created successfully.");
    }

    public function show(User $user)
    {
        $user->load(['branch', 'roles', 'permissions']);
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $branches = Branch::active()->get();
        $roles    = Role::all();
        return view('users.edit', compact('user', 'branches', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,'.$user->id,
            'phone'     => 'nullable|string|max:20',
            'password'  => 'nullable|string|min:8|confirmed',
            'branch_id' => 'required|exists:branches,id',
            'role'      => 'required|exists:roles,name',
        ]);

        $updateData = [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'branch_id' => $data['branch_id'],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);
        $user->syncRoles([$data['role']]);

        return redirect()->route('users.index')->with('success', "User {$user->name} updated.");
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted.');
    }

    public function toggleActive(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }
        $user->update(['is_active' => ! $user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$user->name} {$status}.");
    }
}
