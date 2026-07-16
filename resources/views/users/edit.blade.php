@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-base-100 rounded-xl shadow-md p-6 border border-base-200/50">
        <h1 class="text-2xl font-bold mb-6">Edit User: {{ $user->name }}</h1>

        <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="form-control">
                <label class="label"><span class="label-text font-medium">Full Name <span class="text-error">*</span></span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                       class="input input-bordered @error('name') input-error @enderror" required>
                @error('name') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-medium">Email <span class="text-error">*</span></span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="input input-bordered @error('email') input-error @enderror" required>
                @error('email') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-medium">Role <span class="text-error">*</span></span></label>
                <select name="role" class="select select-bordered @error('role') select-error @enderror" required>
                    <option value="">Select role...</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ (old('role', $user->roles->first()->name ?? '') == $role->name) ? 'selected' : '' }}>
                        {{ ucfirst($role->name) }}
                    </option>
                    @endforeach
                </select>
                @error('role') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            @if(auth()->user()->isSuperAdmin())
            <div class="form-control">
                <label class="label"><span class="label-text font-medium">Company <span class="text-error">*</span></span></label>
                <select name="company_id" class="select select-bordered @error('company_id') select-error @enderror" required>
                    <option value="">Select company...</option>
                    @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ (old('company_id', $user->company_id) == $company->id) ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                    @endforeach
                </select>
                @error('company_id') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
            </div>
            @endif

            <div class="form-control">
                <label class="label"><span class="label-text font-medium">New Password (leave blank to keep current)</span></label>
                <input type="password" name="password" class="input input-bordered @error('password') input-error @enderror">
                <input type="password" name="password_confirmation" class="input input-bordered mt-2" placeholder="Confirm password">
                @error('password') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="flex gap-3 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary gap-2">
                    <i class="fas fa-save"></i> Update User
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
