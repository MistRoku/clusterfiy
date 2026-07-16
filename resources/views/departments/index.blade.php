@extends('layouts.app')

@section('title', 'Departments')

@section('content')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">Departments</h1>
            <p class="text-sm opacity-60">Manage your company departments</p>
        </div>
        @can('create', App\Models\Department::class)
            <a href="{{ route('departments.create') }}" class="btn btn-primary gap-2">
                <i class="fas fa-plus"></i> New Department
            </a>
        @endcan
    </div>

    <div class="overflow-x-auto bg-base-100 rounded-xl shadow-md border border-base-200/50">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Manager</th>
                    <th>Tasks</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $dept)
                    <tr>
                        <td class="font-medium">{{ $dept->name }}</td>
                        <td>{{ $dept->manager->name ?? 'None' }}</td>
                        <td>{{ $dept->tasks()->count() }}</td>
                        <td>
                            <span class="badge {{ $dept->is_active ? 'badge-success' : 'badge-error' }}">
                                {{ $dept->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-1">
                                <a href="{{ route('departments.edit', $dept) }}" class="btn btn-info btn-xs">Edit</a>
                                <form method="POST" action="{{ route('departments.destroy', $dept) }}" class="inline"
                                    onsubmit="return confirm('Delete this department?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-error btn-xs">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center opacity-50 py-8">No departments found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $departments->links() }}
    </div>
@endsection
