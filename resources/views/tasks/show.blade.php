@extends('layouts.app')

@section('title', $task->title)

@section('content')
    <div x-data="{ openTimer: {{ $openTimer ? 'true' : 'false' }} }">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
            <h1 class="text-2xl font-bold">{{ $task->title }}</h1>
            <form method="POST" action="{{ route('tasks.update-status', $task) }}" class="mt-2 sm:mt-0">
                @csrf
                <select name="status" onchange="this.form.submit()"
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm px-3 py-1">
                    @foreach (['todo', 'in_progress', 'in_review', 'blocked', 'done'] as $status)
                        <option value="{{ $status }}" {{ $task->status == $status ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div><strong>Priority:</strong> {{ ucfirst($task->priority) }}</div>
            <div><strong>Due:</strong> {{ $task->due_date ? $task->due_date->format('Y-m-d') : 'No due date' }}</div>
            <div><strong>Department:</strong> {{ $task->department->name ?? 'None' }}</div>
            <div><strong>Assigned To:</strong> {{ $task->assignee->name ?? 'Unassigned' }}</div>
        </div>

        <div class="mt-4">
            <h3 class="font-semibold">Description</h3>
            <p class="whitespace-pre-wrap">{{ $task->description ?? 'No description' }}</p>
        </div>

        <!-- Time Tracking with Alpine -->
        <div class="mt-6 bg-white dark:bg-gray-800 p-4 rounded shadow">
            <h3 class="text-lg font-semibold mb-2">Time Tracking</h3>
            <div>
                <template x-if="openTimer">
                    <button
                        @click="fetch('{{ route('tasks.stop-timer', $task) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(() => location.reload())"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">Stop
                        Timer</button>
                </template>
                <template x-if="!openTimer">
                    <button
                        @click="fetch('{{ route('tasks.start-timer', $task) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(() => location.reload())"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">Start
                        Timer</button>
                </template>
            </div>
            <ul class="mt-2 text-sm">
                @foreach ($task->timeEntries as $entry)
                    <li>{{ $entry->started_at->format('H:i') }} –
                        {{ $entry->ended_at ? $entry->ended_at->format('H:i') : 'ongoing' }}
                        ({{ $entry->duration_hours ?? '0' }}h)</li>
                @endforeach
            </ul>
        </div>

        <!-- Comments -->
        <div class="mt-6">
            <h3 class="text-lg font-semibold">Comments</h3>
            <div class="space-y-2 mt-2 max-h-96 overflow-y-auto">
                @foreach ($task->comments as $comment)
                    <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded">
                        <strong>{{ $comment->user->name }}</strong> <span
                            class="text-xs">{{ $comment->created_at->diffForHumans() }}</span>
                        <p class="mt-1">{{ $comment->body }}</p>
                    </div>
                @endforeach
            </div>
            <form method="POST" action="{{ route('tasks.add-comment', $task) }}" class="mt-3">
                @csrf
                <textarea name="body"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm p-2"
                    rows="3" placeholder="Add a comment..."></textarea>
                <x-primary-button class="mt-2">{{ __('Post Comment') }}</x-primary-button>
            </form>
        </div>

        <!-- Status History -->
        <div class="mt-6">
            <h3 class="text-lg font-semibold">Status History</h3>
            <ul class="list-disc ml-5">
                @foreach ($task->statusChanges as $change)
                    <li>{{ $change->from_status ?? 'Start' }} → {{ $change->to_status }} by
                        {{ $change->changedBy->name ?? 'System' }} at {{ $change->created_at->format('Y-m-d H:i') }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
