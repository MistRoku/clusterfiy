<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Department;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Department $department): bool
    {
        return $user->isSuperAdmin() || (int) $user->company_id === (int) $department->company_id;
    }

    public function create(User $user): bool
    {
        // owner + manager can create departments; members cannot.
        return $user->isSuperAdmin() || $user->hasRole(['company_admin', 'manager']);
    }

    public function update(User $user, Department $department): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ((int) $user->company_id !== (int) $department->company_id) return false;
        return $user->hasRole(['company_admin', 'manager']);
    }

    public function delete(User $user, Department $department): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ((int) $user->company_id !== (int) $department->company_id) return false;
        if ($department->tasks()->exists()) return false;
        return $user->hasRole('company_admin');
    }
}
