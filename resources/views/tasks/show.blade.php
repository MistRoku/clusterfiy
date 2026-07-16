@extends('layouts.app')

@section('title', $task->title)

@section('content')
<div x-data="taskShow()" x-init="init()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">{{ $task->title }}</h1>
            <div class="flex items-center gap-3 mt-1">
                <span class="badge" :class="{
                    'badge-neutral': '{{ $task->status }}' === 'todo',
                    'badge-primary': '{{ $task->status }}' === 'in_progress',
                    'badge-secondary': '{{ $task->status }}' === 'in_review',
                    'badge-error': '{{ $task->status }}' === 'blocked',
                    'badge-success': '{{ $task->status }}' === 'done'
                }">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                <span class="badge" :class="{
                    'badge-ghost': '{{ $task->priority }}' === 'low',
                    'badge-info': '{{ $task->priority }}' === 'medium',
                    'badge-warning': '{{ $task->priority }}' === 'high',
                    'badge-error': '{{ $task->priority }}' === 'urgent'
                }">{{ ucfirst($task->priority) }}</span>
                @if($task->isOverdue())
                    <span class="badge badge-error"><i class="fas fa-exclamation-triangle mr-1"></i> Overdue</span>
                @endif
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-info btn-sm gap-2">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="inline" onsubmit="return confirm('Delete this task?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-error btn-sm gap-2">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Status Change -->
    <div class="bg-base-100 rounded-xl shadow-md p-4 mb-6 border border-base-200/50">
        <form method="POST" action="{{ route('tasks.update-status', $task) }}" class="flex items-center gap-4 flex-wrap">
            @csrf
            <label class="text-sm font-medium">Change Status:</label>
            <select name="status" onchange="this.form.submit()" class="select select-bordered select-sm">
                @foreach(['todo','in_progress','in_review','blocked','done'] as $status)
                <option value="{{ $status }}" {{ $task->status == $status ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Review Actions -->
    @if($task->status === 'in_progress' && auth()->user()->can('update', $task))
    <div class="bg-base-100 rounded-xl shadow-md p-4 mb-6 border border-base-200/50">
        <form method="POST" action="{{ route('tasks.submit-review', $task) }}">
            @csrf
            <button type="submit" class="btn btn-warning btn-sm gap-2">
                <i class="fas fa-check-double"></i> Submit for Review
            </button>
        </form>
    </div>
    @endif

    @if($task->status === 'in_review' && auth()->user()->can('approve', $task))
    <div class="bg-base-100 rounded-xl shadow-md p-4 mb-6 border border-base-200/50">
        <div class="flex flex-wrap gap-3">
            <form method="POST" action="{{ route('tasks.approve', $task) }}">
                @csrf
                <button type="submit" class="btn btn-success btn-sm gap-2">
                    <i class="fas fa-check"></i> Approve
                </button>
            </form>
            <form method="POST" action="{{ route('tasks.reject', $task) }}" class="flex items-center gap-2 flex-wrap">
                @csrf
                <input type="text" name="rejection_reason" placeholder="Reason (optional)..."
                       class="input input-bordered input-sm w-48">
                <button type="submit" class="btn btn-error btn-sm gap-2" onclick="return confirm('Reject this task?')">
                    <i class="fas fa-times"></i> Reject
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-base-100 rounded-xl shadow-md p-4 border border-base-200/50">
            <h3 class="text-sm font-medium opacity-60 mb-2">Description</h3>
            <p class="whitespace-pre-wrap">{{ $task->description ?? 'No description' }}</p>
        </div>
        <div class="bg-base-100 rounded-xl shadow-md p-4 border border-base-200/50">
            <h3 class="text-sm font-medium opacity-60 mb-2">Details</h3>
            <dl class="grid grid-cols-2 gap-2 text-sm">
                <dt class="opacity-60">Department</dt>
                <dd>{{ $task->department->name ?? 'None' }}</dd>
                <dt class="opacity-60">Assigned To</dt>
                <dd>{{ $task->assignee->name ?? 'Unassigned' }}</dd>
                <dt class="opacity-60">Due Date</dt>
                <dd>{{ $task->due_date ? $task->due_date->format('Y-m-d') : 'None' }}</dd>
                <dt class="opacity-60">Estimated Hours</dt>
                <dd>{{ $task->estimated_hours ?? 'N/A' }}</dd>
                <dt class="opacity-60">Actual Hours</dt>
                <dd>{{ $task->actual_hours ?? 'N/A' }}</dd>
                <dt class="opacity-60">Created By</dt>
                <dd>{{ $task->creator->name }}</dd>
                <dt class="opacity-60">Created At</dt>
                <dd>{{ $task->created_at->format('Y-m-d H:i') }}</dd>
            </dl>
        </div>
    </div>

    <!-- Time Tracking -->
    <div class="bg-base-100 rounded-xl shadow-md p-4 mb-6 border border-base-200/50">
        <div class="flex justify-between items-center mb-3">
            <h3 class="text-lg font-semibold"><i class="fas fa-clock text-primary mr-2"></i>Time Tracking</h3>
            <div>
                @if($openTimer)
                    <button @click="stopTimer()" class="btn btn-error btn-sm gap-2">
                        <i class="fas fa-stop"></i> Stop Timer
                    </button>
                @else
                    <button @click="startTimer()" class="btn btn-success btn-sm gap-2">
                        <i class="fas fa-play"></i> Start Timer
                    </button>
                @endif
            </div>
        </div>
        <div class="space-y-2 max-h-48 overflow-y-auto">
            @foreach($task->timeEntries as $entry)
            <div class="flex justify-between items-center text-sm p-2 bg-base-200/50 rounded-lg">
                <span>{{ $entry->started_at->format('H:i') }} - {{ $entry->ended_at ? $entry->ended_at->format('H:i') : 'Ongoing' }}</span>
                <span class="font-medium">{{ number_format($entry->duration_hours ?? 0, 1) }}h</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Tabs: Comments, History -->
    <div x-data="{ tab: 'comments' }">
        <div class="tabs tabs-boxed bg-base-100 p-1 mb-4 rounded-xl">
            <button class="tab" :class="{ 'tab-active': tab === 'comments' }" @click="tab = 'comments'">
                <i class="fas fa-comments mr-2"></i> Comments ({{ $task->comments->count() }})
            </button>
            <button class="tab" :class="{ 'tab-active': tab === 'history' }" @click="tab = 'history'">
                <i class="fas fa-history mr-2"></i> History
            </button>
        </div>

        <!-- Comments -->
        <div x-show="tab === 'comments'" class="bg-base-100 rounded-xl shadow-md p-4 border border-base-200/50">
            <div class="space-y-4 max-h-80 overflow-y-auto custom-scrollbar mb-4">
                @foreach($task->comments as $comment)
                <div class="flex gap-3">
                    <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold">
                            {{ substr($comment->user->name, 0, 1) }}
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-medium">{{ $comment->user->name }}</span>
                            <span class="text-xs opacity-50">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm mt-1">{{ $comment->body }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('tasks.add-comment', $task) }}" class="flex gap-3">
                @csrf
                <textarea name="body" class="textarea textarea-bordered flex-1" rows="2" placeholder="Add a comment..."></textarea>
                <button type="submit" class="btn btn-primary btn-sm self-end">Post</button>
            </form>
        </div>

        <!-- History -->
        <div x-show="tab === 'history'" class="bg-base-100 rounded-xl shadow-md p-4 border border-base-200/50">
            <div class="space-y-2 max-h-80 overflow-y-auto custom-scrollbar">
                @foreach($task->statusChanges as $change)
                <div class="flex items-center gap-3 text-sm p-2 bg-base-200/50 rounded-lg">
                    <span class="badge badge-ghost">{{ $change->from_status ?? 'Created' }}</span>
                    <i class="fas fa-arrow-right opacity-50"></i>
                    <span class="badge badge-primary">{{ $change->to_status }}</span>
                    <span class="opacity-50">by {{ $change->changedBy->name ?? 'System' }}</span>
                    <span class="opacity-40 text-xs">{{ $change->created_at->format('Y-m-d H:i') }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function taskShow() {
        return {
            init() {},
            startTimer() {
                fetch('{{ route('tasks.start-timer', $task) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                }).then(() => window.location.reload());
            },
            stopTimer() {
                fetch('{{ route('tasks.stop-timer', $task) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                }).then(() => window.location.reload());
            }
        }
    }
</script>
@endpush
@endsection