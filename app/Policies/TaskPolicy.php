<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return true; // all authenticated users can see the list
    }

    public function view(User $user, Task $task)
    {
        return $user->company_id === $task->company_id || $user->isSuperAdmin();
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create tasks') || $user->isSuperAdmin();
    }

    public function update(User $user, Task $task)
    {
        if ($user->id === $task->created_by)
            return true;
        if ($user->hasPermissionTo('edit tasks') && $user->company_id === $task->company_id)
            return true;
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Task $task)
    {
        if ($user->hasPermissionTo('delete tasks') && $user->company_id === $task->company_id)
            return true;
        return $user->isSuperAdmin();
    }
}
