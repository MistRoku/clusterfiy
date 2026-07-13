@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">Dashboard</h1>
            <p class="text-sm opacity-60">
                Welcome back, {{ auth()->user()->name }}!
                @if($role === 'super_admin' && ($isGlobalView ?? false))
                    <span class="badge badge-info ml-2">Global View</span>
                @endif
                @if($role === 'company_admin')
                    <span class="badge badge-primary ml-2">Admin</span>
                @endif
                @if($role === 'manager')
                    <span class="badge badge-secondary ml-2">Manager</span>
                @endif
            </p>
        </div>
        @if(isset($company) && $company)
            <span class="badge badge-primary badge-lg">{{ $company->name }}</span>
        @endif
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat bg-base-100 rounded-box shadow p-4">
            <div class="stat-figure text-primary"><i class="fas fa-tasks text-3xl"></i></div>
            <div class="stat-title">Total Tasks</div>
            <div class="stat-value text-2xl md:text-3xl">{{ $totalTasks ?? 0 }}</div>
            <div class="stat-desc">{{ $completedTasks ?? 0 }} completed</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow p-4">
            <div class="stat-figure text-secondary"><i class="fas fa-spinner text-3xl"></i></div>
            <div class="stat-title">In Progress</div>
            <div class="stat-value text-2xl md:text-3xl">{{ $inProgressTasks ?? 0 }}</div>
            <div class="stat-desc text-error">{{ $blockedTasks ?? 0 }} blocked</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow p-4">
            <div class="stat-figure text-accent"><i class="fas fa-users text-3xl"></i></div>
            <div class="stat-title">Team Members</div>
            <div class="stat-value text-2xl md:text-3xl">{{ $teamMembers ?? 0 }}</div>
            <div class="stat-desc">Active users</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow p-4">
            <div class="stat-figure text-warning"><i class="fas fa-clock text-3xl"></i></div>
            <div class="stat-title">Hours Logged</div>
            <div class="stat-value text-2xl md:text-3xl">{{ number_format($totalHours ?? 0, 1) }}</div>
            <div class="stat-desc">Total tracked time</div>
        </div>
    </div>

    <!-- Chart + My Tasks -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Chart -->
        <div class="bg-base-100 p-4 rounded-box shadow">
            <h3 class="text-lg font-semibold mb-4">Tasks by Status</h3>
            @if($statusLabels->count() > 0)
                <canvas id="statusChart" class="max-w-full" height="200"></canvas>
            @else
                <p class="text-center opacity-50 py-8">No task data available</p>
            @endif
        </div>

        <!-- My Tasks -->
        <div class="bg-base-100 p-4 rounded-box shadow">
            <h3 class="text-lg font-semibold mb-4">My Active Tasks</h3>
            @if($myTasks->count() > 0)
                <div class="space-y-2">
                    @foreach($myTasks as $task)
                        <div class="flex justify-between items-center py-2 border-b border-base-200 last:border-0">
                            <a href="{{ route('tasks.show', $task) }}" class="hover:link-primary">
                                {{ $task->title }}
                            </a>
                            <span class="badge badge-{{ $task->status === 'in_progress' ? 'warning' : ($task->status === 'done' ? 'success' : 'neutral') }}">
                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center opacity-50 py-8">No active tasks assigned to you</p>
            @endif
        </div>
    </div>

    <!-- Team Tasks (Managers & Admins only) -->
    @if(in_array($role, ['manager', 'company_admin']) && isset($teamTasks) && $teamTasks->count() > 0)
    <div class="bg-base-100 p-4 rounded-box shadow">
        <h3 class="text-lg font-semibold mb-4">Team Tasks</h3>
        <div class="space-y-2">
            @foreach($teamTasks as $task)
                <div class="flex justify-between items-center py-2 border-b border-base-200 last:border-0">
                    <a href="{{ route('tasks.show', $task) }}" class="hover:link-primary">
                        {{ $task->title }}
                        <span class="text-xs opacity-50 ml-2">({{ $task->assignee->name ?? 'Unassigned' }})</span>
                    </a>
                    <span class="badge badge-{{ $task->status === 'in_progress' ? 'warning' : ($task->status === 'done' ? 'success' : 'neutral') }}">
                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Recent Activity -->
    <div class="bg-base-100 p-4 rounded-box shadow">
        <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
        @if($recentActivity->count() > 0)
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($recentActivity as $log)
                    <div class="flex items-center gap-3 text-sm py-1 border-b border-base-200 last:border-0">
                        <span class="badge badge-ghost">{{ $log->event }}</span>
                        <span>{{ $log->loggable_type === 'App\Models\Task' ? 'Task' : 'Item' }}</span>
                        <span class="opacity-50">•</span>
                        <span class="opacity-60">{{ $log->created_at->diffForHumans() }}</span>
                        @if($log->user)
                            <span class="opacity-50">by {{ $log->user->name }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-center opacity-50 py-4">No recent activity</p>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($statusLabels->count() > 0)
        const ctx = document.getElementById('statusChart').getContext('2d');
        const labels = @json($statusLabels);
        const data = @json($statusCounts);

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Tasks',
                    data: data,
                    backgroundColor: ['#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444', '#10b981', '#6b7280'],
                    borderWidth: 2,
                    borderColor: '#1e293b'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                }
            }
        });
        @endif
    });
</script>
@endpush
