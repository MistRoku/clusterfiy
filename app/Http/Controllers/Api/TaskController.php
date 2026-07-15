<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskController extends Controller
{
    use AuthorizesRequests;
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index()
    {
        $tasks = Task::with(['assignee', 'department'])
            ->where('company_id', session('current_company_id'))
            ->paginate(15);
        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request)
    {
        $validated = $request->validated();
        $validated['company_id'] = session('current_company_id');
        $validated['created_by'] = Auth::id();
        $validated['status'] = 'todo';

        $task = $this->taskService->create($validated);
        return new TaskResource($task);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        return new TaskResource($task->load(['comments.user', 'timeEntries', 'statusChanges']));
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);
        $task = $this->taskService->update($task, $request->validated());
        return new TaskResource($task);
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $this->taskService->delete($task);
        return response()->json(['message' => 'Task deleted']);
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('update', $task);
        $validated = $request->validate(['status' => 'required|in:todo,in_progress,in_review,blocked,done']);
        $task = $this->taskService->updateStatus($task, $validated['status']);
        return new TaskResource($task);
    }

    public function addComment(Request $request, Task $task)
    {
        $this->authorize('view', $task);
        $validated = $request->validate(['body' => 'required|string|max:1000']);
        $comment = $task->comments()->create([
            'user_id' => Auth::id(),
            'body' => $validated['body'],
        ]);
        return response()->json($comment, 201);
    }

    public function startTimer(Request $request, Task $task)
    {
        $this->authorize('view', $task);
        $open = $task->timeEntries()->whereNull('ended_at')->where('user_id', Auth::id())->first();
        if ($open) {
            return response()->json(['message' => 'Timer already running'], 422);
        }
        $entry = $task->timeEntries()->create([
            'user_id' => Auth::id(),
            'started_at' => now(),
            'description' => $request->input('note', 'Started work'),
        ]);
        return response()->json($entry);
    }

    public function stopTimer(Task $task)
    {
        $this->authorize('view', $task);
        $entry = $task->timeEntries()->whereNull('ended_at')->where('user_id', Auth::id())->first();
        if (!$entry) {
            return response()->json(['message' => 'No active timer'], 422);
        }
        $entry->ended_at = now();
        $entry->duration_hours = $entry->started_at->diffInHours($entry->ended_at);
        $entry->save();
        return response()->json($entry);
    }
}
