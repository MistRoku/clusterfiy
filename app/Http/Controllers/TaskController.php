<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Department;
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
        $companyId = session('current_company_id');
        $tasks = Task::where('company_id', $companyId)->paginate(10);
        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        $companyId = session('current_company_id');
        $users = User::where('company_id', $companyId)->get();
        $departments = Department::where('company_id', $companyId)->get();
        return view('tasks.create', compact('users', 'departments'));
    }

    public function store(StoreTaskRequest $request)
    {
        $validated = $request->validated();
        $validated['company_id'] = session('current_company_id');
        $validated['created_by'] = Auth::id();
        $validated['status'] = 'todo';

        $this->taskService->create($validated);

        return redirect()->route('tasks.index')->with('success', 'Task created.');
    }

    public function show(Task $task)
    {
        $task->load(['comments.user', 'statusChanges.changedBy', 'timeEntries']);
        $openTimer = $task->timeEntries()->whereNull('ended_at')->where('user_id', Auth::id())->first();
        return view('tasks.show', compact('task', 'openTimer'));
    }

    public function edit(Task $task)
    {
        Gate::authorize('update', $task);
        $companyId = session('current_company_id');
        $users = User::where('company_id', $companyId)->get();
        $departments = Department::where('company_id', $companyId)->get();
        return view('tasks.edit', compact('task', 'users', 'departments'));
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $validated = $request->validated();
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
        $validated = $request->validate(['status' => 'required|in:todo,in_progress,in_review,blocked,done']);
        $this->taskService->updateStatus($task, $validated['status']);
        return back()->with('success', 'Status updated.');
    }

    public function addComment(Request $request, Task $task)
    {
        $this->authorize('view', $task);
        $body = $request->validate(['body' => 'required|string|max:1000'])['body'];
        $task->comments()->create([
            'user_id' => Auth::id(),
            'body' => $body,
        ]);
        return back()->with('success', 'Comment added.');
    }

    public function startTimer(Request $request, Task $task)
    {
        $this->authorize('view', $task);
        $open = $task->timeEntries()->whereNull('ended_at')->where('user_id', Auth::id())->first();
        if ($open) {
            return back()->with('error', 'You already have an open timer for this task.');
        }
        $task->timeEntries()->create([
            'user_id' => Auth::id(),
            'started_at' => now(),
            'description' => $request->input('note', 'Started work'),
        ]);
        return back()->with('success', 'Timer started.');
    }

    public function stopTimer(Task $task)
    {
        $this->authorize('update', $task);
        if ($task->status !== 'in_progress') {
            return back()->with('error', 'Only tasks in progress can be submitted for review.');
        }
        $task->status = 'in_review';
        $task->save();
        return back()->with('success', 'Task submitted for review.');
    }

    public function approve(Task $task)
    {
        $this->authorize('approve', $task);
        if ($task->status !== 'in_review') {
            return back()->with('error', 'Only tasks in review can be approved.');
        }
        $task->status = 'done';
        $task->completed_at = now();
        $task->save();
        return back()->with('success', 'Task approved.');
    }

    public function reject(Request $request, Task $task)
    {
        $this->authorize('approve', $task);
        if ($task->status !== 'in_review') {
            return back()->with('error', 'Only tasks in review can be rejected.');
        }
        $validated = $request->validate(['rejection_reason' => 'nullable|string|max:500']);
        $task->status = 'in_progress';
        $task->save();
        if (!empty($validated['rejection_reason'])) {
            $task->comments()->create([
                'user_id' => Auth::id(),
                'body' => 'Rejected: ' . $validated['rejection_reason'],
            ]);
        }
        return back()->with('success', 'Task rejected.');
    }
}
