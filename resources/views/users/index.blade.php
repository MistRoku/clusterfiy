@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">Team Members</h1>
            <p class="text-sm opacity-60">Manage your team members and their roles</p>
        </div>
        @can('create', App\Models\User::class)
            <a href="{{ route('users.create') }}" class="btn btn-primary gap-2">
                <i class="fas fa-user-plus"></i> Add User
            </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($users as $user)
            <div
                class="bg-base-100 rounded-xl shadow-md p-5 border border-base-200/50 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center gap-4 mb-3">
                    <div class="avatar placeholder">
                        <div
                            class="bg-primary text-primary-content rounded-full w-12 h-12 flex items-center justify-center text-lg font-bold">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold">{{ $user->name }}</h3>
                        <p class="text-sm opacity-60">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <span
                            class="badge
                    @if ($user->hasRole('company_admin')) badge-primary
                    @elseif($user->hasRole('manager')) badge-secondary
                    @else badge-neutral @endif">
                            {{ ucfirst($user->roles->first()->name ?? 'Employee') }}
                        </span>
                    </div>
                    <div class="flex gap-1">
                        @can('update', $user)
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-info btn-xs">Edit</a>
                        @endcan
                        @can('delete', $user)
                            <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline"
                                onsubmit="return confirm('Remove this user?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-error btn-xs">Remove</button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center opacity-50 py-12">
                <i class="fas fa-users text-5xl opacity-20 mb-4"></i>
                <p>No users found</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endsection
