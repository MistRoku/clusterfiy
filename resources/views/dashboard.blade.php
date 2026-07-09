@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container mx-auto">
        <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
        @if (isset($company))
            <p class="mb-4">Welcome to <strong>{{ $company->name }}</strong></p>
        @endif
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 p-5 rounded shadow">
                <h2 class="text-lg font-semibold mb-3">My Active Tasks</h2>
                @forelse($myTasks as $task)
                    <div class="border-b dark:border-gray-700 py-2 flex justify-between">
                        <a href="{{ route('tasks.show', $task) }}"
                            class="text-indigo-600 hover:underline">{{ $task->title }}</a>
                        <span class="text-sm text-gray-500">{{ ucfirst($task->status) }}</span>
                    </div>
                @empty
                    <p class="text-gray-500">No active tasks.</p>
                @endforelse
            </div>
            <div class="bg-white dark:bg-gray-800 p-5 rounded shadow">
                <h2 class="text-lg font-semibold mb-3">Recent Company Tasks</h2>
                @forelse($recentTasks as $task)
                    <div class="border-b dark:border-gray-700 py-2">{{ $task->title }}</div>
                @empty
                    <p class="text-gray-500">No recent tasks.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
