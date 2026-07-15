<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function generate(array $filters): array
    {
        $companyId = session('current_company_id');
        $query = Task::where('company_id', $companyId);

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
            'by_assignee' => $tasks->groupBy('assignee.name')->map->count(),
            'completion_rate' => $tasks->count() > 0 ? round(($tasks->where('status', 'done')->count() / $tasks->count()) * 100, 1) : 0,
            'total_hours' => TimeEntry::whereHas('task', function($q) use ($companyId, $filters) {
                $q->where('company_id', $companyId);
                if (!empty($filters['date_from'])) $q->whereDate('created_at', '>=', $filters['date_from']);
                if (!empty($filters['date_to'])) $q->whereDate('created_at', '<=', $filters['date_to']);
            })->sum('duration_hours') ?? 0,
        ];
    }

    public function chartData($dateFrom = null, $dateTo = null): array
    {
        $companyId = session('current_company_id');
        $query = Task::where('company_id', $companyId);
        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
        return $query->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();
    }
}
