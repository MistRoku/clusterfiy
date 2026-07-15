<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CompanyController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Company::class, 'company');
    }

    public function index()
    {
        $companies = Company::withCount(['users', 'tasks'])
            ->with('creator')
            ->paginate(15);
        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();
        $validated['is_active'] = true;

        $company = Company::create($validated);

        // Create company admin user
        $adminEmail = 'admin@' . $company->subdomain . '.com';
        $admin = \App\Models\User::create([
            'name' => $company->name . ' Admin',
            'email' => $adminEmail,
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'is_master_admin' => true,
        ]);
        $admin->assignRole('company_admin');

        return redirect()->route('companies.index')
            ->with('success', "Company created. Admin: $adminEmail / password");
    }

    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validated();
        $company->update($validated);
        Cache::forget("company_{$company->subdomain}");
        return redirect()->route('companies.index')->with('success', 'Company updated.');
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return redirect()->route('companies.index')->with('success', 'Company deleted.');
    }

        public function toggleStatus(Company $company)
    {
        $company->update(['is_active' => !$company->is_active]);
        Cache::forget("company_{$company->subdomain}");
        return back()->with('success', 'Company status updated.');
    }
}
