<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
        public function index()
    {
        $companyId = session('current_company_id');
        $users = User::where('company_id', $companyId)->paginate(10);
        $roles = Role::where('name', '!=', 'super_admin')->get();
        return view('users.index', compact('users', 'roles'));
    }

    public function assignRole(Request $request, User $user)
    {
        Gate::authorize('manage users', $user);
        $role = $request->input('role');
        $user->syncRoles([$role]);
        return back()->with('success', 'Role updated.');
    }

    public function destroy(User $user)
    {
        Gate::authorize('manage users', $user);
        $user->delete();
        return back()->with('success', 'User removed.');
    }
}
