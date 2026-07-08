<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company = $user->currentCompany;

        // Fallback to querying tasks by assigned user if the relationship/method isn't defined on User
        $myTasks = Task::where('assigned_to', $user->id)
            ->whereNotIn('status', ['done'])
            ->limit(10)
            ->get();
        $recentTasks = $company ? Task::where('company_id', $company->id)->latest()->limit(5)->get() : collect();

        return view('dashboard', compact('myTasks', 'recentTasks', 'company'));
    }
}
