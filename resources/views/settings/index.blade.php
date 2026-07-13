@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold">Settings</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Company Settings (Company Admin only) -->
        @if(auth()->user()->hasRole('company_admin') || auth()->user()->isSuperAdmin())
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title"><i class="fas fa-building text-primary mr-2"></i> Company Settings</h2>
                <p class="text-sm opacity-60">Manage your company details</p>
                <div class="divider"></div>
                <form method="POST" action="{{ route('settings.company.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="form-control">
                        <label class="label"><span class="label-text">Company Name</span></label>
                        <input type="text" name="name" value="{{ $company->name ?? '' }}" class="input input-bordered w-full">
                    </div>
                    <div class="form-control mt-4">
                        <label class="label"><span class="label-text">Timezone</span></label>
                        <select name="timezone" class="select select-bordered w-full">
                            @foreach(['UTC', 'America/New_York', 'Europe/London', 'Asia/Tokyo', 'Australia/Sydney'] as $tz)
                            <option value="{{ $tz }}" {{ ($company->timezone ?? 'UTC') == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary mt-4">Update Company</button>
                </form>
            </div>
        </div>
        @endif

        <!-- User Settings (Everyone) -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title"><i class="fas fa-user-cog text-primary mr-2"></i> User Settings</h2>
                <p class="text-sm opacity-60">Manage your account</p>
                <div class="divider"></div>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="form-control">
                        <label class="label"><span class="label-text">Name</span></label>
                        <input type="text" name="name" value="{{ auth()->user()->name }}" class="input input-bordered w-full">
                    </div>
                    <div class="form-control mt-4">
                        <label class="label"><span class="label-text">Email</span></label>
                        <input type="email" name="email" value="{{ auth()->user()->email }}" class="input input-bordered w-full">
                    </div>
                    <button type="submit" class="btn btn-primary mt-4">Update Profile</button>
                </form>

                <div class="divider">Password</div>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="form-control">
                        <label class="label"><span class="label-text">New Password</span></label>
                        <input type="password" name="password" class="input input-bordered w-full">
                    </div>
                    <div class="form-control mt-4">
                        <label class="label"><span class="label-text">Confirm Password</span></label>
                        <input type="password" name="password_confirmation" class="input input-bordered w-full">
                    </div>
                    <button type="submit" class="btn btn-secondary mt-4">Update Password</button>
                </form>
            </div>
        </div>

        <!-- Theme Settings -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title"><i class="fas fa-palette text-primary mr-2"></i> Theme Settings</h2>
                <p class="text-sm opacity-60">Customize your experience</p>
                <div class="divider"></div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Dark Mode</span></label>
                    <div x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
                         x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); document.documentElement.classList.toggle('dark', val); })">
                        <input type="checkbox" x-model="darkMode" class="toggle toggle-primary" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
