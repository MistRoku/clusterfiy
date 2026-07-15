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
        if (!Auth::user()->isSuperAdmin())
            abort(403);
        session(['current_company_id' => $companyId]);
        return back()->with('success', 'Switched to ' . $company->name);
    }

    public function reset()
    {
        session()->forget('current_company_id');
        return back()->with('success', 'Reset to global view');
    }
}
