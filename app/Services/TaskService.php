<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskStatusChange;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskStatusChanged;
use App\Support\CompanyContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class TaskService
{
    /**
     * Allowed status transitions.
     * backlog/todo -> in_progress -> review/in_review -> done,
     * done -> in_progress (reopen), cancelled from any except done.
     */
    public const TRANSITIONS = [
        'todo' => ['in_progress', 'blocked', 'cancelled'],
        'backlog' => ['in_progress', 'cancelled'],
        'in_progress' => ['in_review', 'review', 'blocked', 'done', 'cancelled', 'todo'],
        'in_review' => ['done', 'in_progress', 'blocked', 'cancelled'],
        'review' => ['done', 'in_progress', 'blocked', 'cancelled'],
        'blocked' => ['todo', 'in_progress', 'cancelled'],
        'done' => ['in_progress'],
        'cancelled' => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        $from = $from === 'backlog' ? 'todo' : $from;
        // normalise review alias
        $toNorm = $to === 'review' ? 'in_review' : $to;
        $fromNorm = $from === 'review' ? 'in_review' : $from;
        if ($fromNorm === $toNorm) {
            return true;
        }
        return in_array($to, self::TRANSITIONS[$from] ?? [], true)
            || in_array($toNorm, self::TRANSITIONS[$fromNorm] ?? [], true);
    }

    public function create(array $data): Task
    {
        return DB::transaction(function () use ($data) {
            $data['company_id'] ??= CompanyContext::get();
            $this->assertAssigneeInCompany($data['assigned_to'] ?? null, $data['company_id'] ?? null);
            $this->assertDueDate($data);
            $task = Task::create($data);
            if ($task->assigned_to) {
                $task->assignee?->notify(new TaskAssigned($task));
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
            if (array_key_exists('status', $data) && $data['status'] !== $oldStatus) {
                $this->transition($task, $data['status']);
                unset($data['status']);
            }
            if (array_key_exists('assigned_to', $data)) {
                $this->assertAssigneeInCompany($data['assigned_to'], $task->company_id);
            }
            if (array_key_exists('due_date', $data)) {
                $this->assertDueDate(array_merge($task->toArray(), $data));
            }
            $task->update($data);
            if ($oldAssignee !== $task->assigned_to && $task->assigned_to) {
                $task->assignee?->notify(new TaskAssigned($task));
            }
            $this->clearCache();
            return $task->fresh();
        });
    }

    /** Enforced status machine. Throws 422 on illegal transitions. */
    public function transition(Task $task, string $to, ?string $comment = null): Task
    {
        $from = $task->status;
        if (! self::canTransition($from, $to)) {
            throw ValidationException::withMessages([
                'status' => ["Illegal transition from {$from} to {$to}."],
            ]);
        }

        return DB::transaction(function () use ($task, $from, $to, $comment) {
            $oldStatus = $task->status;
            $task->status = $to;
            if ($to === 'done') {
                $task->completed_at = now();
            }
            $task->save();

            TaskStatusChange::create([
                'task_id' => $task->id,
                'company_id' => $task->company_id,
                'from_status' => $oldStatus,
                'to_status' => $to,
                'changed_by' => auth()->id(),
                'comment' => $comment,
            ]);

            $task->assignee?->notify(new TaskStatusChanged($task, $oldStatus));
            $this->clearCache();
            return $task->fresh();
        });
    }

    /** Legacy entry point now routed through transition(). */
    public function updateStatus(Task $task, string $status): Task
    {
        return $this->transition($task, $status);
    }

    public function delete(Task $task): bool
    {
        return DB::transaction(function () use ($task) {
            $task->delete();
            $this->clearCache();
            return true;
        });
    }

    public function getStats(): array
    {
        $companyId = CompanyContext::get();
        return Cache::remember("task_stats_{$companyId}", 300, function () {
            return [
                'by_status' => Task::selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
                'by_priority' => Task::selectRaw('priority, count(*) as count')
                    ->groupBy('priority')
                    ->pluck('count', 'priority'),
                'overdue' => Task::where('due_date', '<', now())
                    ->where('status', '!=', 'done')
                    ->count(),
            ];
        });
    }

    protected function assertAssigneeInCompany(mixed $userId, mixed $companyId): void
    {
        if (! $userId) {
            return;
        }
        $user = \App\Models\User::whereKey($userId)->first();
        if (! $user) {
            throw ValidationException::withMessages(['assigned_to' => ['Assignee not found.']]);
        }
        if ($companyId && ! $user->belongsToCompany((int) $companyId)) {
            throw ValidationException::withMessages(['assigned_to' => ['Assignee must be an active member of the current company.']]);
        }
    }

    protected function assertDueDate(array $data): void
    {
        if (empty($data['due_date'])) {
            return;
        }
        $created = isset($data['created_at']) ? \Carbon\Carbon::parse($data['created_at']) : now();
        if (\Carbon\Carbon::parse($data['due_date'])->lt($created->startOfDay())) {
            throw ValidationException::withMessages(['due_date' => ['Due date cannot precede creation date.']]);
        }
    }

    private function clearCache(): void
    {
        Cache::forget('task_stats_' . CompanyContext::get());
    }
}
