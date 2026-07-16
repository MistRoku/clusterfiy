@extends('layouts.app')

@section('title', 'Create Task')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-base-100 rounded-xl shadow-md p-6 border border-base-200/50">
            <h1 class="text-2xl font-bold mb-6">Create New Task</h1>

            <form method="POST" action="{{ route('tasks.store') }}" class="space-y-4">
                @csrf

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Title <span
                                class="text-error">*</span></span></label>
                    <input type="text" name="title" value="{{ old('title') }}"
                        class="input input-bordered @error('title') input-error @enderror" placeholder="Enter task title"
                        required>
                    @error('title')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Description</span></label>
                    <textarea name="description" rows="4"
                        class="textarea textarea-bordered @error('description') textarea-error @enderror"
                        placeholder="Describe the task...">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Department</span></label>
                        <select name="department_id"
                            class="select select-bordered @error('department_id') select-error @enderror">
                            <option value="">None</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Assign To</span></label>
                        <select name="assigned_to"
                            class="select select-bordered @error('assigned_to') select-error @enderror">
                            <option value="">Unassigned</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Priority <span
                                    class="text-error">*</span></span></label>
                        <select name="priority" class="select select-bordered @error('priority') select-error @enderror"
                            required>
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                        @error('priority')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Due Date</span></label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}"
                            class="input input-bordered @error('due_date') input-error @enderror">
                        @error('due_date')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Due Time</span></label>
                        <input type="time" name="due_time" value="{{ old('due_time') }}"
                            class="input input-bordered @error('due_time') input-error @enderror">
                        @error('due_time')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Estimated Hours</span></label>
                    <input type="number" step="0.5" name="estimated_hours" value="{{ old('estimated_hours') }}"
                        class="input input-bordered @error('estimated_hours') input-error @enderror"
                        placeholder="e.g., 2.5">
                    @error('estimated_hours')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex gap-3 pt-4 border-t border-base-200">
                    <button type="submit" class="btn btn-primary gap-2">
                        <i class="fas fa-save"></i> Create Task
                    </button>
                    <a href="{{ route('tasks.index') }}" class="btn btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
