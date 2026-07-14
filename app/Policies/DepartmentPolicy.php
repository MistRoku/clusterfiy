<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Department;

class DepartmentPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('company_admin') || $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->hasRole('company_admin') || $user->isSuperAdmin();
    }

    public function update(User $user, Department $department): bool
    {
        return $user->company_id === $department->company_id && ($user->hasRole('company_admin') || $user->isSuperAdmin());
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->company_id === $department->company_id && ($user->hasRole('company_admin') || $user->isSuperAdmin());
    }
}
