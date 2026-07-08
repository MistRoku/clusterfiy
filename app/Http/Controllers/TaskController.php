<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i',
            'priority' => 'required|in:low,medium,high,urgent',
            'estimated_hours' => 'nullable|numeric|min:0|max:999.99',
        ]);

        $task = Task::create([
            'company_id' => session('current_company_id'),
            'department_id' => $validated['department_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'],
            'due_time' => $validated['due_time'],
            'estimated_hours' => $validated['estimated_hours'],
            'created_by' => Auth::id(),
            'assigned_to' => $validated['assigned_to'],
            'status' => 'todo',
        ]);

        if ($validated['assigned_to']) {
            $task->assignees()->attach($validated['assigned_to'], ['assigned_by' => Auth::id()]);
        }

        return redirect()->route('tasks.index')->with('success', 'Task created.');
    }

    public function show(Task $task)
    {
        Gate::authorize('view', $task);
        $task->load('comments.user', 'statusChanges.changedBy', 'timeEntries');
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

    public function update(Request $request, Task $task)
    {
        Gate::authorize('update', $task);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i',
            'priority' => 'required|in:low,medium,high,urgent',
            'estimated_hours' => 'nullable|numeric|min:0|max:999.99',
        ]);

        $task->update($validated);
        if ($validated['assigned_to']) {
            $task->assignees()->sync([$validated['assigned_to']]);
        } else {
            $task->assignees()->detach();
        }

        return redirect()->route('tasks.index')->with('success', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        Gate::authorize('delete', $task);
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        Gate::authorize('update', $task);
        $status = $request->validate(['status' => 'required|in:todo,in_progress,in_review,blocked,done'])['status'];
        $task->status = $status;
        $task->save();
        return back()->with('success', 'Status updated.');
    }

    public function addComment(Request $request, Task $task)
    {
        Gate::authorize('view', $task);
        $body = $request->validate(['body' => 'required|string|max:1000'])['body'];
        $task->comments()->create([
            'user_id' => Auth::id(),
            'body' => $body,
        ]);
        return back()->with('success', 'Comment added.');
    }

    public function startTimer(Request $request, Task $task)
    {
        Gate::authorize('view', $task);
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
        Gate::authorize('view', $task);
        $entry = $task->timeEntries()->whereNull('ended_at')->where('user_id', Auth::id())->first();
        if (!$entry) {
            return back()->with('error', 'No active timer.');
        }
        $entry->ended_at = now();
        $entry->duration_hours = $entry->started_at->diffInHours($entry->ended_at);
        $entry->save();
        return back()->with('success', 'Timer stopped.');
    }
}
