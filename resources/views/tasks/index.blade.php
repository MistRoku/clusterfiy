@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
    <div x-data="taskManager()" x-init="init()">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold">Tasks</h1>
                <p class="text-sm opacity-60">Manage your team's tasks</p>
            </div>
            <a href="{{ route('tasks.create') }}" class="btn btn-primary gap-2">
                <i class="fas fa-plus"></i> New Task
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-base-100 rounded-xl shadow-md p-4 mb-6 border border-base-200/50">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 opacity-50"></i>
                    <input type="text" x-model="filters.search" placeholder="Search tasks..."
                        class="input input-bordered w-full pl-10" @input="applyFilters()">
                </div>
                <select x-model="filters.status" class="select select-bordered w-full" @change="applyFilters()">
                    <option value="">All Statuses</option>
                    <option value="todo">Todo</option>
                    <option value="in_progress">In Progress</option>
                    <option value="in_review">In Review</option>
                    <option value="blocked">Blocked</option>
                    <option value="done">Done</option>
                </select>
                <select x-model="filters.priority" class="select select-bordered w-full" @change="applyFilters()">
                    <option value="">All Priorities</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
                <select x-model="filters.assignee" class="select select-bordered w-full" @change="applyFilters()">
                    <option value="">All Assignees</option>
                    @foreach (App\Models\User::where('company_id', session('current_company_id'))->get() as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <button @click="resetFilters()" class="btn btn-ghost">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
        </div>

        <!-- Task Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="task in filteredTasks" :key="task.id">
                <div
                    class="bg-base-100 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5 border border-base-200/50">
                    <div class="flex justify-between items-start mb-2">
                        <a :href="'/tasks/' + task.id" class="text-lg font-semibold hover:text-primary transition-colors"
                            x-text="task.title"></a>
                        <span class="badge"
                            :class="{
                                'badge-neutral': task.status === 'todo',
                                'badge-primary': task.status === 'in_progress',
                                'badge-secondary': task.status === 'in_review',
                                'badge-error': task.status === 'blocked',
                                'badge-success': task.status === 'done'
                            }"
                            x-text="task.status.replace('_', ' ')"></span>
                    </div>
                    <p class="text-sm opacity-60 line-clamp-2 mb-3" x-text="task.description || 'No description'"></p>
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <span class="badge"
                                :class="{
                                    'badge-ghost': task.priority === 'low',
                                    'badge-info': task.priority === 'medium',
                                    'badge-warning': task.priority === 'high',
                                    'badge-error': task.priority === 'urgent'
                                }"
                                x-text="task.priority"></span>
                            <span class="opacity-50 text-xs" x-text="task.due_date || 'No due date'"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs opacity-50" x-text="task.assignee?.name || 'Unassigned'"></span>
                            <div class="avatar placeholder w-6 h-6 rounded-full bg-primary text-primary-content flex items-center justify-center text-xs font-bold"
                                x-text="task.assignee?.name?.charAt(0) || '?'"></div>
                        </div>
                    </div>
                    <div class="divider my-2"></div>
                    <div class="flex justify-end gap-2">
                        <a :href="'/tasks/' + task.id" class="btn btn-ghost btn-xs">View</a>
                        <a :href="'/tasks/' + task.id + '/edit'" class="btn btn-info btn-xs">Edit</a>
                        <button @click="deleteTask(task.id)" class="btn btn-error btn-xs">Delete</button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="filteredTasks.length === 0" class="text-center py-12">
            <i class="fas fa-tasks text-5xl opacity-20 mb-4"></i>
            <p class="text-lg opacity-60">No tasks found</p>
            <p class="text-sm opacity-40">Try adjusting your filters or create a new task</p>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $tasks->links() }}
        </div>
    </div>

    @push('scripts')
        <script>
            function taskManager() {
                return {
                    tasks: @json($tasks->items()),
                    filteredTasks: [],
                    filters: {
                        search: '',
                        status: '',
                        priority: '',
                        assignee: ''
                    },
                    init() {
                        this.filteredTasks = this.tasks;
                    },
                    applyFilters() {
                        this.filteredTasks = this.tasks.filter(task => {
                            const matchSearch = task.title.toLowerCase().includes(this.filters.search.toLowerCase()) ||
                                (task.description && task.description.toLowerCase().includes(this.filters.search
                                    .toLowerCase()));
                            const matchStatus = !this.filters.status || task.status === this.filters.status;
                            const matchPriority = !this.filters.priority || task.priority === this.filters.priority;
                            const matchAssignee = !this.filters.assignee || task.assignee?.id == this.filters.assignee;
                            return matchSearch && matchStatus && matchPriority && matchAssignee;
                        });
                    },
                    resetFilters() {
                        this.filters = {
                            search: '',
                            status: '',
                            priority: '',
                            assignee: ''
                        };
                        this.applyFilters();
                    },
                    deleteTask(id) {
                        if (confirm('Are you sure you want to delete this task?')) {
                            fetch('/tasks/' + id, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                }
                            }).then(response => {
                                if (response.ok) {
                                    window.location.reload();
                                }
                            });
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection
