<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $companyId = session('current_company_id');
        $totalTasks = Task::where('company_id', $companyId)->count();
        $completedTasks = Task::where('company_id', $companyId)->where('status', 'done')->count();
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
        $timePerUser = TimeEntry::select('user_id', DB::raw('SUM(duration_hours) as total_hours'))
            ->whereHas('task', fn($q) => $q->where('company_id', $companyId))
            ->groupBy('user_id')
            ->with('user')
            ->get();
        $tasksByStatus = Task::where('company_id', $companyId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
        return view('reports.index', compact('totalTasks', 'completedTasks', 'completionRate', 'timePerUser', 'tasksByStatus'));
    }
}
