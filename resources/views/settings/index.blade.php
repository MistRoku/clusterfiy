@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">Settings</h1>
            <p class="text-sm opacity-60">Manage your account and company preferences</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Profile Settings -->
            <div class="bg-base-100 rounded-xl shadow-md p-6 border border-base-200/50">
                <h2 class="text-lg font-semibold mb-4">
                    <i class="fas fa-user-cog text-primary mr-2"></i> Profile Settings
                </h2>
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div class="form-control">
                        <label class="label"><span class="label-text">Name</span></label>
                        <input type="text" name="name" value="{{ auth()->user()->name }}"
                            class="input input-bordered w-full">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Email</span></label>
                        <input type="email" name="email" value="{{ auth()->user()->email }}"
                            class="input input-bordered w-full">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-full">Update Profile</button>
                </form>
            </div>

            <!-- Password Settings -->
            <div class="bg-base-100 rounded-xl shadow-md p-6 border border-base-200/50">
                <h2 class="text-lg font-semibold mb-4">
                    <i class="fas fa-lock text-primary mr-2"></i> Password
                </h2>
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div class="form-control">
                        <label class="label"><span class="label-text">New Password</span></label>
                        <input type="password" name="password" class="input input-bordered w-full">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Confirm Password</span></label>
                        <input type="password" name="password_confirmation" class="input input-bordered w-full">
                    </div>
                    <button type="submit" class="btn btn-secondary btn-sm w-full">Update Password</button>
                </form>
            </div>

            <!-- Theme Settings -->
            <div class="bg-base-100 rounded-xl shadow-md p-6 border border-base-200/50">
                <h2 class="text-lg font-semibold mb-4">
                    <i class="fas fa-palette text-primary mr-2"></i> Theme
                </h2>
                <div x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val);
                    document.documentElement.classList.toggle('dark', val); })">
                    <div class="flex items-center justify-between">
                        <span class="text-sm">Dark Mode</span>
                        <input type="checkbox" x-model="darkMode" class="toggle toggle-primary">
                    </div>
                </div>
            </div>

            <!-- Company Settings (Admin only) -->
            @if (auth()->user()->hasRole('company_admin') || auth()->user()->isSuperAdmin())
                <div class="bg-base-100 rounded-xl shadow-md p-6 border border-base-200/50">
                    <h2 class="text-lg font-semibold mb-4">
                        <i class="fas fa-building text-primary mr-2"></i> Company Settings
                    </h2>
                    <form method="POST" action="{{ route('settings.company.update') }}" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div class="form-control">
                            <label class="label"><span class="label-text">Company Name</span></label>
                            <input type="text" name="name" value="{{ $company->name ?? '' }}"
                                class="input input-bordered w-full">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-full">Update Company</button>
                    </form>
                </div>
            @endif

            <!-- Danger Zone -->
            <div class="bg-base-100 rounded-xl shadow-md p-6 border border-error/20">
                <h2 class="text-lg font-semibold mb-4 text-error">
                    <i class="fas fa-exclamation-triangle text-error mr-2"></i> Danger Zone
                </h2>
                <p class="text-sm opacity-60 mb-3">Permanently delete your account and all associated data.</p>
                <button class="btn btn-error btn-sm" onclick="document.getElementById('delete-account-form').submit();">
                    <i class="fas fa-trash mr-2"></i> Delete Account
                </button>
                <form id="delete-account-form" method="POST" action="{{ route('profile.destroy') }}" class="hidden">
                    @csrf @method('DELETE')
                </form>
            </div>
        </div>
    </div>
@endsection
