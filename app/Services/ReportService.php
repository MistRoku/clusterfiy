<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TimeEntry;

class ReportService
{
    public function generate(array $filters): array
    {
        // Tenancy comes from CompanyScope; no manual company_id filter.
        $query = Task::with(['assignee', 'department']);

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['assignee'])) {
            $query->where('assigned_to', $filters['assignee']);
        }

        $tasks = $query->get();

        return [
            'total' => $tasks->count(),
            'by_status' => $tasks->groupBy('status')->map->count(),
            'by_priority' => $tasks->groupBy('priority')->map->count(),
            'by_assignee' => $tasks->groupBy(fn ($t) => $t->assignee?->name ?? 'Unassigned')->map->count(),
            'completion_rate' => $tasks->count() > 0 ? round(($tasks->where('status', 'done')->count() / $tasks->count()) * 100, 1) : 0,
            'total_hours' => TimeEntry::sum('duration_hours') ?? 0,
            'tasks' => $tasks,
        ];
    }

    public function chartData($dateFrom = null, $dateTo = null): array
    {
        $query = Task::query();
        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
        return $query->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();
    }
}
