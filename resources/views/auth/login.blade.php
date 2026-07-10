@extends('layouts.guest') <!-- we need a guest layout without sidebar -->

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500">
    <div class="card w-96 bg-base-100 shadow-2xl">
        <div class="card-body">
            <div class="text-center mb-6">
                <img src="{{ asset('images/logo-full.svg') }}" alt="Clusterfiy" class="h-12 mx-auto">
                <h2 class="text-2xl font-bold mt-2">Welcome Back</h2>
                <p class="text-sm text-base-content/70">Sign in to your account</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Email</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" class="input input-bordered @error('email') input-error @enderror" required>
                    @error('email') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text">Password</span>
                    </label>
                    <input type="password" name="password" class="input input-bordered @error('password') input-error @enderror" required>
                    @error('password') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    <label class="label">
                        <a href="{{ route('password.request') }}" class="label-text-alt link link-hover">Forgot password?</a>
                    </label>
                </div>

                <div class="form-control mt-6">
                    <button type="submit" class="btn btn-primary w-full">Login</button>
                </div>
            </form>

            <div class="divider">OR</div>
            <div class="text-center text-sm">
                Don't have an account? <a href="{{ route('register') }}" class="link link-primary">Register</a>
            </div>
        </div>
    </div>
</div>
@endsection
