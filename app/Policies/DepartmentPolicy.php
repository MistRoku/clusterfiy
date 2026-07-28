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
        return $user->isSuperAdmin() || $user->company_id === $department->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('company_admin') || $user->isSuperAdmin();
    }

    public function update(User $user, Department $department): bool
    {
        if ($user->isSuperAdmin()) return true;
        return $user->company_id === $department->company_id && $user->hasRole('company_admin');
    }

    public function delete(User $user, Department $department): bool
    {
        if ($user->isSuperAdmin())
            return true;
        if ($user->company_id !== $department->company_id)
            return false;

        // Prevent deleting department with tasks
        if ($department->tasks()->exists()) {
            return false;
        }

        return $user->hasRole('company_admin');
    }
}
