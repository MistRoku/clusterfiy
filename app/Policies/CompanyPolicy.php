<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Company;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Company $company): bool
    {
        return $user->isSuperAdmin() || $user->belongsToCompany($company->id);
    }

    public function create(User $user): bool
    {
        // Plan limit enforced in controller: Free = 1 company per user.
        return true;
    }

    public function update(User $user, Company $company): bool
    {
        if ($user->isSuperAdmin()) return true;
        return $user->belongsToCompany($company->id) && $user->hasRole('company_admin');
    }

    public function delete(User $user, Company $company): bool
    {
        if ($user->isSuperAdmin()) return true;
        return $user->belongsToCompany($company->id) && $user->hasRole('company_admin');
    }

    public function manageBilling(User $user, Company $company): bool
    {
        if ($user->isSuperAdmin()) return true;
        return $user->belongsToCompany($company->id) && $user->hasRole('company_admin');
    }

    public function manageMembers(User $user, Company $company): bool
    {
        if ($user->isSuperAdmin()) return true;
        return $user->belongsToCompany($company->id) && $user->hasRole(['company_admin', 'manager']);
    }
}
