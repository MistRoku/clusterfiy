<?php

namespace App\Exports;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TasksExport implements FromCollection, WithHeadings, WithMapping
{
    protected $dateFrom;
    protected $dateTo;

    public function __construct($dateFrom = null, $dateTo = null)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function collection()
    {
        $query = Task::with(['assignee', 'department'])
            ->where('company_id', session('current_company_id'));

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['ID', 'Title', 'Status', 'Priority', 'Assignee', 'Department', 'Due Date', 'Created At'];
    }

    public function map($task): array
    {
        return [
            $task->id,
            $task->title,
            $task->status,
            $task->priority,
            $task->assignee->name ?? 'Unassigned',
            $task->department->name ?? 'None',
            $task->due_date?->format('Y-m-d'),
            $task->created_at->format('Y-m-d H:i'),
        ];
    }
}