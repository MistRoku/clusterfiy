<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Notifications\UserInvited;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index()
    {
        $companyId = session('current_company_id');
        $users = User::where('company_id', $companyId)->paginate(10);
        $roles = Role::where('name', '!=', 'super_admin')->get();
        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::where('name', '!=', 'super_admin')->get();
        $companies = Auth::user()->isSuperAdmin() ? Company::all() : collect();
        return view('users.create', compact('roles', 'companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'role' => 'required|exists:roles,name',
            'company_id' => Auth::user()->isSuperAdmin() ? 'required|exists:companies,id' : 'nullable',
        ]);

        $companyId = Auth::user()->isSuperAdmin()
            ? $validated['company_id']
            : session('current_company_id');

        $password = Str::random(12);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'company_id' => $companyId,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($validated['role']);
        $user->notify(new UserInvited($user, $password));

        return redirect()->route('users.index')
            ->with('success', "User created! Temporary password: $password");
    }

    public function edit(User $user)
    {
        $roles = Role::where('name', '!=', 'super_admin')->get();
        $companies = Auth::user()->isSuperAdmin() ? Company::all() : collect();
        return view('users.edit', compact('user', 'roles', 'companies'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name',
            'company_id' => Auth::user()->isSuperAdmin() ? 'required|exists:companies,id' : 'nullable',
        ]);

        $companyId = Auth::user()->isSuperAdmin()
            ? $validated['company_id']
            : $user->company_id;

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'company_id' => $companyId,
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function assignRole(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $role = $request->input('role');
        if ($role) {
            $user->syncRoles([$role]);
        } else {
            $user->syncRoles([]);
        }
        return back()->with('success', 'Role updated.');
    }

    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        User::destroy($user->id);
        return back()->with('success', 'User removed.');
    }
}
