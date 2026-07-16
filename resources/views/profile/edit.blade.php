@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-base-100 rounded-xl shadow-md p-6 border border-base-200/50">
            <h1 class="text-2xl font-bold mb-6">Edit Profile</h1>

            @if (session('success'))
                <div class="alert alert-success mb-4">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Full Name <span
                                class="text-error">*</span></span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="input input-bordered @error('name') input-error @enderror" required>
                    @error('name')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Email <span
                                class="text-error">*</span></span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        class="input input-bordered @error('email') input-error @enderror" required>
                    @error('email')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="divider">Change Password</div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">New Password (leave blank to keep
                            current)</span></label>
                    <input type="password" name="password"
                        class="input input-bordered @error('password') input-error @enderror">
                    @error('password')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Confirm Password</span></label>
                    <input type="password" name="password_confirmation" class="input input-bordered">
                </div>

                <div class="flex gap-3 pt-4 border-t border-base-200">
                    <button type="submit" class="btn btn-primary gap-2">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
