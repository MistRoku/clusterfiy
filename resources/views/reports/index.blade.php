@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Reports</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 p-4 rounded shadow">
            <h3 class="text-lg font-semibold">Total Tasks</h3>
            <p class="text-3xl">{{ $totalTasks }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded shadow">
            <h3 class="text-lg font-semibold">Completed</h3>
            <p class="text-3xl">{{ $completedTasks }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded shadow">
            <h3 class="text-lg font-semibold">Completion Rate</h3>
            <p class="text-3xl">{{ $completionRate }}%</p>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 p-4 rounded shadow">
            <h3 class="text-lg font-semibold mb-2">Time per User</h3>
            <ul class="list-disc ml-5">
                @foreach ($timePerUser as $entry)
                    <li>{{ $entry->user->name }}: {{ number_format($entry->total_hours, 1) }}h</li>
                @endforeach
            </ul>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded shadow">
            <h3 class="text-lg font-semibold mb-2">Tasks by Status</h3>
            <ul class="list-disc ml-5">
                @foreach ($tasksByStatus as $status)
                    <li>{{ ucfirst($status->status) }}: {{ $status->count }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
