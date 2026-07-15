<?php

namespace App\Http\Controllers\Api;

use App\Models\Company;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        return response()->json(Company::withCount('users')->get());
    }

    public function switch(Request $request)
    {
        $validated = $request->validate(['company_id' => 'required|exists:companies,id']);
        session(['current_company_id' => $validated['company_id']]);
        return response()->json(['message' => 'Switched to company']);
    }

    public function current()
    {
        $company = Company::find(session('current_company_id'));
        return response()->json($company);
    }
}
