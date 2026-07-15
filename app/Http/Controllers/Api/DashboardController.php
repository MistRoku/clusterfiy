<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $companyId = session('current_company_id');
        $metrics = $this->dashboardService->getMetrics($companyId);
        $recentTasks = \App\Models\Task::where('company_id', $companyId)
            ->with('assignee')
            ->latest()
            ->limit(5)
            ->get();
        return response()->json([
            'metrics' => $metrics,
            'recent_tasks' => $recentTasks,
        ]);
    }
}
