<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasRole('company_admin') || $user->hasRole('manager');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->isSuperAdmin())
            return true;
        if ($user->company_id !== $model->company_id)
            return false;
        return $user->hasAnyRole(['company_admin', 'manager']) || $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('company_admin') || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->isSuperAdmin())
            return true;
        if ($user->company_id !== $model->company_id)
            return false;
        return $user->hasRole('company_admin') || $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id)
            return false; // Cannot delete self
        if ($user->isSuperAdmin())
            return true;
        if ($user->company_id !== $model->company_id)
            return false;

        // Prevent deleting last company admin
        if ($model->hasRole('company_admin')) {
            $adminCount = User::where('company_id', $model->company_id)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'company_admin');
                })
                ->count();
            if ($adminCount <= 1)
                return false;
        }

        return $user->hasRole('company_admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }


}
