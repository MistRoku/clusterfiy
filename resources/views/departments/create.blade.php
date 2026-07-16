@extends('layouts.app')

@section('title', 'Create Department')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-base-100 rounded-xl shadow-md p-6 border border-base-200/50">
            <h1 class="text-2xl font-bold mb-6">Create Department</h1>

            <form method="POST" action="{{ route('departments.store') }}" class="space-y-4">
                @csrf

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Department Name <span
                                class="text-error">*</span></span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="input input-bordered @error('name') input-error @enderror" required>
                    @error('name')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Description</span></label>
                    <textarea name="description" rows="3"
                        class="textarea textarea-bordered @error('description') textarea-error @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Manager</span></label>
                    <select name="manager_id" class="select select-bordered @error('manager_id') select-error @enderror">
                        <option value="">None</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ old('manager_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('manager_id')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex gap-3 pt-4 border-t border-base-200">
                    <button type="submit" class="btn btn-primary gap-2">
                        <i class="fas fa-save"></i> Create Department
                    </button>
                    <a href="{{ route('departments.index') }}" class="btn btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
