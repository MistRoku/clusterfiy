<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanySwitchController extends Controller
{
    public function switch(Request $request)
    {
        $companyId = (int) $request->input('company_id');
        // Deliberate exception: bypass global scope to resolve the target company.
        $company = Company::withoutGlobalScope(CompanyScope::class)->findOrFail($companyId);
        $user = Auth::user();
        if (! $user->isSuperAdmin() && ! $user->belongsToCompany($company->id)) {
            abort(403, 'You are not a member of this company.');
        }
        session(['current_company_id' => $company->id]);
        CompanyContext::set($company->id);
        return back()->with('success', 'Switched to ' . $company->name);
    }

    public function reset()
    {
        session()->forget('current_company_id');
        CompanyContext::forget();
        return back()->with('success', 'Reset to global view');
    }
}
