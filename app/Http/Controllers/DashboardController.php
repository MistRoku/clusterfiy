<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Company;
use App\Models\TimeEntry;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company = null;
        $companyId = null;
        $isGlobalView = false;

        // ─── Determine which company to show ───
        if ($user->isSuperAdmin()) {
            if (session('current_company_id')) {
                $company = Company::query()->find(session('current_company_id'));
                if ($company) {
                    $companyId = $company->id;
                } else {
                    $isGlobalView = true;
                }
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

        if ($isGlobalView) {
            $totalTasks = Task::query()->count('*');
            $completedTasks = Task::query()->where('status', 'done')->count('*');
            $inProgressTasks = Task::query()->where('status', 'in_progress')->count('*');
            $blockedTasks = Task::query()->where('status', 'blocked')->count('*');
            $teamMembers = User::query()->where('is_super_admin', false)->count('*');
            $totalHours = TimeEntry::query()->sum('duration_hours') ?? 0;

            $statuses = Task::query()
                ->selectRaw('status, count(*) as count', [])
                ->groupBy('status')
                ->pluck('count', 'status');
            $statusLabels = $statuses->keys()->map(fn ($s) => ucfirst(str_replace('_', ' ', $s)));
            $statusCounts = $statuses->values();

            $recentActivity = ActivityLog::query()
                ->with('user')
                ->latest()
                ->limit(10)
                ->get();
        } elseif ($companyId) {
            $totalTasks = Task::query()->where('company_id', $companyId)->count();
            $completedTasks = Task::query()
                ->where('company_id', $companyId)
                ->where('status', 'done')
                ->count();
            $inProgressTasks = Task::query()
                ->where('company_id', $companyId)
                ->where('status', 'in_progress')
                ->count();
            $blockedTasks = Task::query()
                ->where('company_id', $companyId)
                ->where('status', 'blocked')
                ->count();
            $teamMembers = User::query()->where('company_id', $companyId)->count();
            $totalHours = TimeEntry::query()
                ->whereHas('task', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->sum('duration_hours') ?? 0;

            $statuses = Task::query()
                ->where('company_id', $companyId)
                ->selectRaw('status, count(*) as count', [])
                ->groupBy('status')
                ->pluck('count', 'status');
            $statusLabels = $statuses->keys()->map(fn ($s) => ucfirst(str_replace('_', ' ', $s)));
            $statusCounts = $statuses->values();

            $recentActivity = ActivityLog::query()
                ->whereHasMorph('loggable', [Task::class], function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->with('user')
                ->latest()
                ->limit(10)
                ->get();
        }

        $myTasks = $user->tasksAssigned()
            ->whereNotIn('status', ['done'])
            ->limit(10)
            ->get();

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
            'isGlobalView'
        ));
    }
}
