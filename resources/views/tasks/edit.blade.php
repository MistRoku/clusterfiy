@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Edit Task</h1>
    <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="title" :value="__('Title')" />
            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $task->title)" required />
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" :value="__('Description')" />
            <textarea id="description" name="description" rows="3"
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $task->description) }}</textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="department_id" :value="__('Department')" />
                <select id="department_id" name="department_id"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    <option value="">None</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}"
                            {{ old('department_id', $task->department_id) == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('department_id')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="assigned_to" :value="__('Assign To')" />
                <select id="assigned_to" name="assigned_to"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    <option value="">Unassigned</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}"
                            {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('assigned_to')" class="mt-2" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <x-input-label for="due_date" :value="__('Due Date')" />
                <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full" :value="old('due_date', optional($task->due_date)->format('Y-m-d'))" />
                <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="due_time" :value="__('Due Time')" />
                <x-text-input id="due_time" name="due_time" type="time" class="mt-1 block w-full" :value="old('due_time', optional($task->due_time)->format('H:i'))" />
                <x-input-error :messages="$errors->get('due_time')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="priority" :value="__('Priority')" />
                <select id="priority" name="priority"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>Medium
                    </option>
                    <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>High
                    </option>
                    <option value="urgent" {{ old('priority', $task->priority) == 'urgent' ? 'selected' : '' }}>Urgent
                    </option>
                </select>
                <x-input-error :messages="$errors->get('priority')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="estimated_hours" :value="__('Estimated Hours')" />
            <x-text-input id="estimated_hours" name="estimated_hours" type="number" step="0.5"
                class="mt-1 block w-full" :value="old('estimated_hours', $task->estimated_hours)" />
            <x-input-error :messages="$errors->get('estimated_hours')" class="mt-2" />
        </div>

        <div class="flex space-x-3">
            <x-primary-button>{{ __('Update') }}</x-primary-button>
            <a href="{{ route('tasks.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:bg-gray-400 dark:focus:bg-gray-600 active:bg-gray-500 dark:active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">Cancel</a>
        </div>
    </form>
@endsection
