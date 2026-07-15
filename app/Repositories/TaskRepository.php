<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskRepository
{
    public function getFiltered(array $filters): LengthAwarePaginator
    {
        $query = Task::with(['assignee', 'department', 'creator', 'comments'])
            ->where('company_id', session('current_company_id'));

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (!empty($filters['assignee'])) {
            $query->where('assigned_to', $filters['assignee']);
        }
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'LIKE', '%' . $filters['search'] . '%');
            });
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function findWithRelations(int $id): Task
    {
        return Task::with([
            'assignee',
            'department',
            'creator',
            'comments.user',
            'comments.replies.user',
            'attachments',
            'timeEntries',
            'statusChanges.changedBy'
        ])->findOrFail($id);
    }
}
