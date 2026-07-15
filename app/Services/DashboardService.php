<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Company;

class DashboardService
{
    public function getMetrics($companyId)
    {
        $cacheKey = "dashboard_metrics_{$companyId}";
        return Cache::remember($cacheKey, 300, function () use ($companyId) {
            return [
                'total_tasks' => Task::where('company_id', $companyId)->count(),
                'completed_tasks' => Task::where('company_id', $companyId)->where('status', 'done')->count(),
                'in_progress_tasks' => Task::where('company_id', $companyId)->where('status', 'in_progress')->count(),
                'blocked_tasks' => Task::where('company_id', $companyId)->where('status', 'blocked')->count(),
                'team_members' => User::where('company_id', $companyId)->count(),
                'total_hours' => TimeEntry::whereHas('task', fn($q) => $q->where('company_id', $companyId))->sum('duration_hours') ?? 0,
                'status_distribution' => Task::where('company_id', $companyId)
                    ->selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
            ];
        });
    }
}
