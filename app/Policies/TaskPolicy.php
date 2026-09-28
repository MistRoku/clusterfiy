<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        return $user->isSuperAdmin() || (int) $user->company_id === (int) $task->company_id;
    }

    public function create(User $user): bool
    {
        // owner + manager create; members can create only via self-assign flow if given permission
        return $user->isSuperAdmin() || $user->hasPermissionTo('create tasks') || $user->hasRole(['company_admin', 'manager', 'employee']);
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ((int) $user->company_id !== (int) $task->company_id) return false;
        if ($user->id === $task->created_by) return true;
        if ($user->id === $task->assigned_to) return true;
        return $user->hasRole(['company_admin', 'manager']);
    }

    public function delete(User $user, Task $task): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ((int) $user->company_id !== (int) $task->company_id) return false;
        if ($user->id === $task->created_by) return true;
        return $user->hasRole('company_admin');
    }

    public function approve(User $user, Task $task): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ((int) $user->company_id !== (int) $task->company_id) return false;
        return $user->hasRole(['manager', 'company_admin']);
    }
}
