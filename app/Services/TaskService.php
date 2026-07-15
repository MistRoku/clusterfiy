<?php

namespace App\Services;

use App\Models\Task;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TaskService
{
    public function create(array $data): Task
    {
        return DB::transaction(function () use ($data) {
            $task = Task::create($data);
            if ($task->assigned_to) {
                $task->assignee->notify(new TaskAssigned($task));
            }
            $this->clearCache();
            return $task;
        });
    }

    public function update(Task $task, array $data): Task
    {
        $oldStatus = $task->status;
        $oldAssignee = $task->assigned_to;

        return DB::transaction(function () use ($task, $data, $oldStatus, $oldAssignee) {
            $task->update($data);
            if ($oldStatus !== $task->status) {
                $task->assignee?->notify(new TaskStatusChanged($task, $oldStatus));
            }
            if ($oldAssignee !== $task->assigned_to && $task->assigned_to) {
                $task->assignee->notify(new TaskAssigned($task));
            }
            $this->clearCache();
            return $task;
        });
    }

    public function delete(Task $task): bool
    {
        return DB::transaction(function () use ($task) {
            $task->delete();
            $this->clearCache();
            return true;
        });
    }

    public function updateStatus(Task $task, string $status): Task
    {
        $oldStatus = $task->status;
        $task->status = $status;
        $task->save();

        if ($oldStatus !== $status) {
            $task->assignee?->notify(new TaskStatusChanged($task, $oldStatus));
        }
        $this->clearCache();
        return $task;
    }

    public function getStats(): array
    {
        $companyId = session('current_company_id');
        return Cache::remember("task_stats_{$companyId}", 300, function () use ($companyId) {
            return [
                'by_status' => Task::where('company_id', $companyId)
                    ->selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
                'by_priority' => Task::where('company_id', $companyId)
                    ->selectRaw('priority, count(*) as count')
                    ->groupBy('priority')
                    ->pluck('count', 'priority'),
                'overdue' => Task::where('company_id', $companyId)
                    ->where('due_date', '<', now())
                    ->where('status', '!=', 'done')
                    ->count(),
            ];
        });
    }

    private function clearCache(): void
    {
        Cache::forget("task_stats_" . session('current_company_id'));
    }
}
