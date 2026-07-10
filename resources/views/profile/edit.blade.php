@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-4">Edit Profile</h2>

                @if (session('success'))
                    <div class="alert alert-success mb-4">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-control">
                        <label class="label"><span class="label-text">Name</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
                            class="input input-bordered @error('name') input-error @enderror">
                        @error('name')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control mt-4">
                        <label class="label"><span class="label-text">Email</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                            class="input input-bordered @error('email') input-error @enderror">
                        @error('email')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control mt-4">
                        <label class="label"><span class="label-text">New Password (leave blank to keep
                                current)</span></label>
                        <input type="password" name="password"
                            class="input input-bordered @error('password') input-error @enderror">
                        @error('password')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control mt-2">
                        <label class="label"><span class="label-text">Confirm Password</span></label>
                        <input type="password" name="password_confirmation" class="input input-bordered">
                    </div>

                    <div class="form-control mt-6">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
