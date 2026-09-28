<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Support\CompanyContext;
use Illuminate\Support\Facades\Auth;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService)
    {
    }

    public function index()
    {
        $user = Auth::user();
        $companyId = CompanyContext::get();
        $company = $companyId ? Company::withoutGlobalScopes()->find($companyId) : null;

        $manager = $this->dashboardService->managerDashboard();
        $member = $this->dashboardService->memberDashboard($user->id);

        $isManager = $user->isSuperAdmin() || $user->hasRole(['company_admin', 'manager']);

        return view('dashboard', array_merge([
            'company' => $company,
            'title' => $company ? ('Dashboard: ' . $company->name) : 'Dashboard',
            'metaDescription' => 'Scoped dashboard for the active company: workload, overdue tasks and recent activity.',
            'isManager' => $isManager,
            'myTasks' => $member['my_open'],
        ], $isManager ? $manager : [
            'by_status' => [],
            'overdue' => $member['my_overdue'],
            'workload' => [],
            'trend' => [],
            'activity' => [],
        ], [
            'member' => $member,
            'trend' => $manager['completed_trend_30d'] ?? [],
        ]));
    }
}
