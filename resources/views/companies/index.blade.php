@extends('layouts.app')

@section('title', 'Companies')

@section('content')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">Companies</h1>
            <p class="text-sm opacity-60">Manage all companies on the platform</p>
        </div>
        <a href="{{ route('companies.create') }}" class="btn btn-primary gap-2">
            <i class="fas fa-plus"></i> New Company
        </a>
    </div>

    <div class="overflow-x-auto bg-base-100 rounded-xl shadow-md border border-base-200/50">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Subdomain</th>
                    <th>Users</th>
                    <th>Tasks</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                    <tr>
                        <td>{{ $company->id }}</td>
                        <td class="font-medium">{{ $company->name }}</td>
                        <td><code class="bg-base-200 px-2 py-1 rounded text-sm">{{ $company->subdomain }}</code></td>
                        <td>{{ $company->users_count }}</td>
                        <td>{{ $company->tasks_count }}</td>
                        <td>
                            <span class="badge {{ $company->is_active ? 'badge-success' : 'badge-error' }}">
                                {{ $company->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-1">
                                <a href="{{ route('companies.edit', $company) }}" class="btn btn-info btn-xs">Edit</a>
                                <form method="POST" action="{{ route('companies.destroy', $company) }}" class="inline"
                                    onsubmit="return confirm('Delete this company? All data will be lost.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-error btn-xs">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center opacity-50 py-8">No companies found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $companies->links() }}
    </div>
@endsection
