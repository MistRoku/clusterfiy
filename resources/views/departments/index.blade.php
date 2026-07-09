@extends('layouts.app')

@section('title', 'Departments')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-bold">Departments</h1>
    <a href="{{ route('departments.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">New Department</a>
</div>
<div class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow">
    <table class="min-w-full">
        <thead>
            <tr class="border-b dark:border-gray-700">
                <th class="px-4 py-2 text-left">Name</th>
                <th class="px-4 py-2 text-left">Manager</th>
                <th class="px-4 py-2 text-left">Status</th>
                <th class="px-4 py-2 text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($departments as $dept)
            <tr class="border-b dark:border-gray-700">
                <td class="px-4 py-2">{{ $dept->name }}</td>
                <td class="px-4 py-2">{{ $dept->manager->name ?? 'None' }}</td>
                <td class="px-4 py-2">{{ $dept->is_active ? 'Active' : 'Inactive' }}</td>
                <td class="px-4 py-2">
                    <a href="{{ route('departments.edit', $dept) }}" class="text-blue-600 hover:underline">Edit</a>
                    <form method="POST" action="{{ route('departments.destroy', $dept) }}" class="inline" onsubmit="return confirm('Delete this department?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline ml-2">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-4 py-4 text-center text-gray-500">No departments.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $departments->links() }}
@endsection
