<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ReportService;
use App\Exports\TasksExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }
    public function index(Request $request)
    {
        $data = $this->reportService->generate($request->all());
        return view('reports.index', $data);
    }

    public function export(Request $request)
    {
        return Excel::download(
            new TasksExport($request->input('date_from'), $request->input('date_to')),
            'tasks-report.xlsx'
        );
    }

    public function data(Request $request)
    {
        return response()->json($this->reportService->chartData(
            $request->input('date_from'),
            $request->input('date_to')
        ));
    }
}
