@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
<div x-data="{
    search: '',
    statusFilter: '',
    tasks: @js($tasks->items())
}" x-init="$watch('search', () => filterTasks()); $watch('statusFilter', () => filterTasks())">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Tasks</h1>
        <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">New Task</a>
    </div>

    <div class="flex flex-wrap gap-4 mb-4">
        <input type="text" x-model="search" placeholder="Search tasks..." class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
        <select x-model="statusFilter" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
            <option value="">All statuses</option>
            <option value="todo">To Do</option>
            <option value="in_progress">In Progress</option>
            <option value="in_review">In Review</option>
            <option value="blocked">Blocked</option>
            <option value="done">Done</option>
        </select>
    </div>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow">
        <table class="min-w-full">
            <thead>
                <tr class="border-b dark:border-gray-700">
                    <th class="px-4 py-2 text-left">Title</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Priority</th>
                    <th class="px-4 py-2 text-left">Due Date</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="task in filteredTasks" :key="task.id">
                    <tr class="border-b dark:border-gray-700">
                        <td class="px-4 py-2" x-text="task.title"></td>
                        <td class="px-4 py-2" x-text="task.status"></td>
                        <td class="px-4 py-2" x-text="task.priority"></td>
                        <td class="px-4 py-2" x-text="task.due_date || '—'"></td>
                        <td class="px-4 py-2">
                            <a :href="'/tasks/' + task.id" class="text-indigo-600 hover:underline">View</a>
                            <a :href="'/tasks/' + task.id + '/edit'" class="text-blue-600 hover:underline ml-2">Edit</a>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    {{ $tasks->links() }}
</div>

@push('scripts')
<script>
function filterTasks() {
    this.filteredTasks = this.tasks.filter(task => {
        const matchesSearch = task.title.toLowerCase().includes(this.search.toLowerCase());
        const matchesStatus = this.statusFilter === '' || task.status === this.statusFilter;
        return matchesSearch && matchesStatus;
    });
}
</script>
@endpush
@endsection
