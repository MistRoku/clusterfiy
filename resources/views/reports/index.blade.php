@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    <div x-data="reportManager()" x-init="init()">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold">Reports</h1>
                <p class="text-sm opacity-60">Analytics and insights for your team</p>
            </div>
            <div class="flex gap-2">
                <button @click="exportReport()" class="btn btn-success gap-2">
                    <i class="fas fa-file-excel"></i> Export
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-base-100 rounded-xl shadow-md p-4 mb-6 border border-base-200/50">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="form-control">
                    <label class="label"><span class="label-text">Date From</span></label>
                    <input type="date" x-model="filters.date_from" class="input input-bordered w-full"
                        @change="applyFilters()">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Date To</span></label>
                    <input type="date" x-model="filters.date_to" class="input input-bordered w-full"
                        @change="applyFilters()">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Status</span></label>
                    <select x-model="filters.status" class="select select-bordered w-full" @change="applyFilters()">
                        <option value="">All</option>
                        <option value="todo">Todo</option>
                        <option value="in_progress">In Progress</option>
                        <option value="in_review">In Review</option>
                        <option value="blocked">Blocked</option>
                        <option value="done">Done</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="stat bg-base-100 rounded-xl shadow-md p-4 border border-base-200/50">
                <div class="stat-title text-sm opacity-60">Total Tasks</div>
                <div class="stat-value text-2xl font-bold" x-text="metrics.total"></div>
            </div>
            <div class="stat bg-base-100 rounded-xl shadow-md p-4 border border-base-200/50">
                <div class="stat-title text-sm opacity-60">Completed</div>
                <div class="stat-value text-2xl font-bold text-success" x-text="metrics.by_status?.done || 0"></div>
            </div>
            <div class="stat bg-base-100 rounded-xl shadow-md p-4 border border-base-200/50">
                <div class="stat-title text-sm opacity-60">Completion Rate</div>
                <div class="stat-value text-2xl font-bold text-primary" x-text="metrics.completion_rate + '%'"></div>
            </div>
            <div class="stat bg-base-100 rounded-xl shadow-md p-4 border border-base-200/50">
                <div class="stat-title text-sm opacity-60">Total Hours</div>
                <div class="stat-value text-2xl font-bold text-warning" x-text="metrics.total_hours"></div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-base-100 rounded-xl shadow-md p-4 border border-base-200/50">
                <h3 class="text-lg font-semibold mb-4">Tasks by Status</h3>
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            <div class="bg-base-100 rounded-xl shadow-md p-4 border border-base-200/50">
                <h3 class="text-lg font-semibold mb-4">Tasks by Priority</h3>
                <div class="h-64">
                    <canvas id="priorityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Productivity Table -->
        <div class="bg-base-100 rounded-xl shadow-md p-4 mt-6 border border-base-200/50">
            <h3 class="text-lg font-semibold mb-4">Productivity by Assignee</h3>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Assignee</th>
                            <th>Tasks</th>
                            <th>Completed</th>
                            <th>Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(count, name) in metrics.by_assignee" :key="name">
                            <tr>
                                <td x-text="name || 'Unassigned'"></td>
                                <td x-text="count"></td>
                                <td x-text="metrics.completed_by_assignee?.[name] || 0"></td>
                                <td>
                                    <span class="badge"
                                        :class="{
                                            'badge-success': (metrics.completed_by_assignee?.[name] || 0) / count *
                                                100 >= 80,
                                            'badge-warning': (metrics.completed_by_assignee?.[name] || 0) / count *
                                                100 >= 50,
                                            'badge-error': (metrics.completed_by_assignee?.[name] || 0) / count * 100 <
                                                50
                                        }"
                                        x-text="Math.round((metrics.completed_by_assignee?.[name] || 0) / count * 100) + '%'">
                                    </span>
                                </td>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function reportManager() {
                return {
                    metrics: @json($metrics ?? []),
                    filters: {
                        date_from: '',
                        date_to: '',
                        status: ''
                    },
                    init() {
                        this.renderCharts();
                    },
                    applyFilters() {
                        const params = new URLSearchParams(this.filters).toString();
                        window.location.href = '{{ route('reports.index') }}?' + params;
                    },
                    exportReport() {
                        const params = new URLSearchParams(this.filters).toString();
                        window.location.href = '{{ route('reports.export') }}?' + params;
                    },
                    renderCharts() {
                        // Status Chart
                        const statusCtx = document.getElementById('statusChart')?.getContext('2d');
                        if (statusCtx && this.metrics.by_status) {
                            const labels = Object.keys(this.metrics.by_status);
                            const data = Object.values(this.metrics.by_status);
                            const colors = ['#6366f1', '#3b82f6', '#8b5cf6', '#ef4444', '#10b981'];

                            new Chart(statusCtx, {
                                type: 'doughnut',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        data: data,
                                        backgroundColor: colors.slice(0, data.length),
                                        borderWidth: 2,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'bottom'
                                        }
                                    }
                                }
                            });
                        }

                        // Priority Chart
                        const priorityCtx = document.getElementById('priorityChart')?.getContext('2d');
                        if (priorityCtx && this.metrics.by_priority) {
                            const labels = Object.keys(this.metrics.by_priority);
                            const data = Object.values(this.metrics.by_priority);
                            const colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'];

                            new Chart(priorityCtx, {
                                type: 'bar',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Tasks',
                                        data: data,
                                        backgroundColor: colors.slice(0, data.length),
                                        borderRadius: 6,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection
