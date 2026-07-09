@extends('layouts.app')

@section('title', 'Companies')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Companies</h1>
        <a href="{{ route('companies.create') }}"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">New
            Company</a>
    </div>
    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow">
        <table class="min-w-full">
            <thead>
                <tr class="border-b dark:border-gray-700">
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Subdomain</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                    <tr class="border-b dark:border-gray-700">
                        <td class="px-4 py-2">{{ $company->id }}</td>
                        <td class="px-4 py-2">{{ $company->name }}</td>
                        <td class="px-4 py-2">{{ $company->subdomain }}</td>
                        <td class="px-4 py-2">{{ $company->is_active ? 'Active' : 'Suspended' }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('companies.edit', $company) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('companies.destroy', $company) }}" class="inline"
                                onsubmit="return confirm('Delete this company? All data will be lost.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline ml-2">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">No companies.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $companies->links() }}
@endsection
