<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;

        public function index()
    {
        $this->authorize('viewAny', User::class);
        $companyId = session('current_company_id');
        $users = User::where('company_id', $companyId)->paginate(10);
        $roles = Role::where('name', '!=', 'super_admin')->get();
        return view('users.index', compact('users', 'roles'));
    }

    public function assignRole(Request $request, User $user)
    {
        $this->authorize('manage users', $user);
        $role = $request->input('role');
        $user->syncRoles([$role]);
        return back()->with('success', 'Role updated.');
    }

    public function destroy(User $user)
    {
        $this->authorize('manage users', $user);
        User::destroy($user->id);
        return back()->with('success', 'User removed.');
    }
}
