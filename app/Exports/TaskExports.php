<?php

namespace App\Exports;

use App\Models\Task;

class TaskExports
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
        // Tenancy via CompanyScope global scope.
        $query = Task::with(['assignee', 'department']);

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
