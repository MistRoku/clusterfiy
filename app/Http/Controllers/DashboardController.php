<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Company;
use App\Models\TimeEntry;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{

    public function __construct(
        private DashboardService $dashboardService
    ) {
    }
    public function index()
    {
        $user = Auth::user();
        $company = null;
        $companyId = null;
        $isGlobalView = false;

        // ─── Determine which company to show ───
        if ($user->isSuperAdmin()) {
            if (session('current_company_id')) {
                $company = Company::find(session('current_company_id'));
                $companyId = $company?->id;
            } else {
                $isGlobalView = true;
            }
        } elseif ($user->company_id) {
            $company = $user->company;
            $companyId = $user->company_id;
        }

        $totalTasks = 0;
        $completedTasks = 0;
        $inProgressTasks = 0;
        $blockedTasks = 0;
        $teamMembers = 0;
        $totalHours = 0;
        $statusLabels = collect([]);
        $statusCounts = collect([]);
        $recentActivity = collect([]);
        $myTasks = collect([]);
        $weeklyData = [];

        if ($isGlobalView) {
            $totalTasks = Task::count();
            $completedTasks = Task::where('status', 'done')->count();
            $inProgressTasks = Task::where('status', 'in_progress')->count();
            $blockedTasks = Task::where('status', 'blocked')->count();
            $teamMembers = User::where('is_super_admin', false)->count();
            $totalHours = TimeEntry::sum('duration_hours') ?? 0;

            $statuses = Task::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');
            $statusLabels = $statuses->keys()->map(fn($s) => ucfirst(str_replace('_', ' ', $s)));
            $statusCounts = $statuses->values();

            $recentActivity = ActivityLog::with('user')->latest()->limit(10)->get();
            $weeklyData = $this->getWeeklyData(null);

        } elseif ($companyId) {
            $totalTasks = Task::where('company_id', $companyId)->count();
            $completedTasks = Task::where('company_id', $companyId)->where('status', 'done')->count();
            $inProgressTasks = Task::where('company_id', $companyId)->where('status', 'in_progress')->count();
            $blockedTasks = Task::where('company_id', $companyId)->where('status', 'blocked')->count();
            $teamMembers = User::where('company_id', $companyId)->count();
            $totalHours = TimeEntry::whereHas('task', fn($q) => $q->where('company_id', $companyId))->sum('duration_hours') ?? 0;

            $statuses = Task::where('company_id', $companyId)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');
            $statusLabels = $statuses->keys()->map(fn($s) => ucfirst(str_replace('_', ' ', $s)));
            $statusCounts = $statuses->values();

            $recentActivity = ActivityLog::whereHasMorph('loggable', [Task::class], fn($q) => $q->where('company_id', $companyId))
                ->with('user')
                ->latest()
                ->limit(10)
                ->get();

            $weeklyData = $this->getWeeklyData($companyId);
        }

        $myTasks = $user->tasksAssigned()
            ->whereNotIn('status', ['done'])
            ->limit(10)
            ->get();

        // Growth metrics (example – replace with real calculation)
        $taskGrowth = 12;
        $completionGrowth = 8;
        $memberGrowth = 2;
        $hoursGrowth = 5;

        return view('dashboard', compact(
            'company',
            'totalTasks',
            'completedTasks',
            'inProgressTasks',
            'blockedTasks',
            'teamMembers',
            'totalHours',
            'statusLabels',
            'statusCounts',
            'recentActivity',
            'myTasks',
            'isGlobalView',
            'weeklyData',
            'taskGrowth',
            'completionGrowth',
            'memberGrowth',
            'hoursGrowth'
        ));
    }

    private function getWeeklyData($companyId)
    {
        $query = Task::query();
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = (clone $query)->whereDate('created_at', $date->format('Y-m-d'))->count();
            $data[] = $count;
        }
        return $data;
    }
}
