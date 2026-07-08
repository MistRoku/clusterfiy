<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanySwitchController extends Controller
{
    public function switch(Request $request)
    {
        $companyId = $request->input('company_id');
        $company = Company::findOrFail($companyId);
        $user = Auth::user();
        if (
            !$user || !(
                (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) ||
                (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) ||
                (isset($user->is_super_admin) && $user->is_super_admin) ||
                (isset($user->is_admin) && $user->is_admin) ||
                (isset($user->role) && in_array($user->role, ['super-admin', 'admin'], true))
            )
        ) {
            abort(403);
        }
        session(['current_company_id' => $companyId]);
        return back()->with('success', 'Switched to ' . $company->name);
    }

    public function reset()
    {
        session()->forget('current_company_id');
        return back()->with('success', 'Reset to global view');
    }
}
