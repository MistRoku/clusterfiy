<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Department;
use App\Notifications\TaskCommented;
use App\Services\TaskService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;

class TaskController extends Controller
{
    use AuthorizesRequests;

    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
        $this->authorizeResource(Task::class, 'task');
    }

    public function index()
    {
        // Tenancy via CompanyScope global scope.
        $tasks = Task::with(['assignee', 'department'])->paginate(10);
        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        $users = User::where('company_id', session('current_company_id'))->get();
        $departments = Department::get();
        return view('tasks.create', compact('users', 'departments'));
    }

    public function store(StoreTaskRequest $request)
    {
        $validated = $request->validated();

        // Assignment rules: members may only self-assign.
        $user = $request->user();
        if (! $user->hasRole(['company_admin', 'manager']) && ! $user->isSuperAdmin()) {
            if (! empty($validated['assigned_to']) && (int) $validated['assigned_to'] !== (int) $user->id) {
                abort(403, 'Members can only assign tasks to themselves.');
            }
        }

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'todo';

        $this->taskService->create($validated);

        return redirect()->route('tasks.index')->with('success', 'Task created.');
    }

    public function show(Task $task)
    {
        // Route-model binding already resolves within CompanyScope (IDOR-safe).
        $task->load(['comments.user', 'statusChanges.changedBy', 'timeEntries']);
        $openTimer = $task->timeEntries()->whereNull('ended_at')->where('user_id', Auth::id())->first();
        return view('tasks.show', compact('task', 'openTimer'));
    }

    public function edit(Task $task)
    {
        Gate::authorize('update', $task);
        $users = User::where('company_id', session('current_company_id'))->get();
        $departments = Department::get();
        return view('tasks.edit', compact('task', 'users', 'departments'));
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $validated = $request->validated();
        $user = $request->user();
        if (! $user->hasRole(['company_admin', 'manager']) && ! $user->isSuperAdmin()) {
            if (array_key_exists('assigned_to', $validated) && (int) $validated['assigned_to'] !== (int) $user->id) {
                abort(403, 'Members can only assign tasks to themselves.');
            }
        }
        $this->taskService->update($task, $validated);
        return redirect()->route('tasks.index')->with('success', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        $this->taskService->delete($task);
        return redirect()->route('tasks.index')->with('success', 'Task deleted.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate(['status' => 'required|string']);
        $this->taskService->transition($task, $validated['status']);
        return back()->with('success', 'Status updated.');
    }

    public function addComment(Request $request, Task $task)
    {
        $this->authorize('view', $task);
        $body = $request->validate(['body' => 'required|string|max:1000'])['body'];
        $comment = $task->comments()->create([
            'company_id' => $task->company_id,
            'user_id' => Auth::id(),
            'body' => $body,
        ]);

        // Notify assignees + creator, excluding the actor.
        $notify = collect([$task->assignee, $task->creator])
            ->filter()
            ->unique('id')
            ->reject(fn ($u) => $u->id === Auth::id());
        foreach ($notify as $u) {
            $u->notify(new TaskCommented($comment));
        }

        return back()->with('success', 'Comment added.');
    }

    public function startTimer(Request $request, Task $task)
    {
        $this->authorize('view', $task);
        $validated = $request->validate([
            'started_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);
        $open = $task->timeEntries()->whereNull('ended_at')->where('user_id', Auth::id())->first();
        if ($open) {
            return back()->with('error', 'You already have an open timer for this task.');
        }
        $task->timeEntries()->create([
            'company_id' => $task->company_id,
            'user_id' => Auth::id(),
            'started_at' => $validated['started_at'] ?? now(),
            'description' => $validated['note'] ?? 'Started work',
        ]);
        return back()->with('success', 'Timer started.');
    }

    public function stopTimer(Request $request, Task $task)
    {
        $this->authorize('view', $task);
        $validated = $request->validate(['ended_at' => ['nullable', 'date', 'after_or_equal:started_at']]);
        $open = $task->timeEntries()->whereNull('ended_at')->where('user_id', Auth::id())->firstOrFail();
        // Users can only edit their own entries.
        if ((int) $open->user_id !== (int) Auth::id()) {
            abort(403);
        }
        $ended = isset($validated['ended_at']) ? \Carbon\Carbon::parse($validated['ended_at']) : now();
        if ($ended->lt($open->started_at)) {
            return back()->withErrors(['ended_at' => 'End time cannot precede start time.']);
        }
        $open->ended_at = $ended;
        $open->duration_hours = $open->started_at->diffInMinutes($ended) / 60;
        $open->save();
        return back()->with('success', 'Timer stopped.');
    }

    public function approve(Task $task)
    {
        $this->authorize('approve', $task);
        $this->taskService->transition($task, 'done');
        return back()->with('success', 'Task approved.');
    }

    public function reject(Request $request, Task $task)
    {
        $this->authorize('approve', $task);
        $validated = $request->validate(['rejection_reason' => 'nullable|string|max:500']);
        $this->taskService->transition($task, 'in_progress', $validated['rejection_reason'] ?? null);
        if (! empty($validated['rejection_reason'])) {
            $task->comments()->create([
                'company_id' => $task->company_id,
                'user_id' => Auth::id(),
                'body' => 'Rejected: ' . $validated['rejection_reason'],
            ]);
        }
        return back()->with('success', 'Task rejected.');
    }
}
