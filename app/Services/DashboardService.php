<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\TimeEntry;
use App\Models\ActivityLog;
use App\Support\CompanyContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function getMetrics($companyId = null)
    {
        $companyId ??= CompanyContext::get();
        $cacheKey = "dashboard_metrics_{$companyId}";
        return Cache::remember($cacheKey, 300, function () {
            // Global scope already restricts to active company; no manual where needed.
            return [
                'total_tasks' => Task::count(),
                'completed_tasks' => Task::where('status', 'done')->count(),
                'in_progress_tasks' => Task::where('status', 'in_progress')->count(),
                'blocked_tasks' => Task::where('status', 'blocked')->count(),
                'team_members' => User::where('company_id', $companyId ?? CompanyContext::get())->count(),
                'total_hours' => TimeEntry::sum('duration_hours') ?? 0,
                'status_distribution' => Task::selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
            ];
        });
    }

    /** Manager/owner dashboard DTO. */
    public function managerDashboard(): array
    {
        $byStatus = Task::with('assignee')->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status');

        $overdue = Task::with(['assignee', 'department'])
            ->where('due_date', '<', now()->toDateString())
            ->where('status', '!=', 'done')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        $workload = Task::selectRaw('assigned_to, count(*) as open_count')
            ->whereNotIn('status', ['done', 'cancelled'])
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to')
            ->with('assignee')
            ->get();

        $trend = Task::selectRaw('DATE(updated_at) as d, count(*) as c')
            ->where('status', 'done')
            ->where('updated_at', '>=', now()->subDays(30))
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('c', 'd');

        $activity = ActivityLog::with('user')->latest()->limit(10)->get();

        return [
            'by_status' => $byStatus,
            'overdue' => $overdue,
            'workload' => $workload,
            'completed_trend_30d' => $trend,
            'activity' => $activity,
        ];
    }

    /** Member dashboard DTO. */
    public function memberDashboard(int $userId): array
    {
        $mine = fn ($q) => $q->where('assigned_to', $userId);

        return [
            'my_open' => Task::with('department')->where('assigned_to', $userId)->whereNotIn('status', ['done', 'cancelled'])->orderBy('due_date')->limit(10)->get(),
            'my_overdue' => Task::where('assigned_to', $userId)->where('due_date', '<', now()->toDateString())->where('status', '!=', 'done')->get(),
            'due_this_week' => Task::where('assigned_to', $userId)->whereBetween('due_date', [now()->toDateString(), now()->addWeek()->toDateString()])->whereNotIn('status', ['done', 'cancelled'])->get(),
            'recent_comments' => \App\Models\Comment::with(['user', 'commentable'])->whereHasMorph('commentable', [Task::class], fn ($q) => $mine($q))->latest()->limit(10)->get(),
        ];
    }
}
