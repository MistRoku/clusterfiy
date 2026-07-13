@extends('layouts.guest')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-base-200 p-4">
        <div class="card w-full max-w-md bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="text-center mb-6">
                    <img src="{{ asset('images/Clusterfiy-Full.png') }}" alt="Clusterfiy" class="h-12 mx-auto"
                        onerror="this.style.display='none'">
                    <h2 class="text-2xl font-bold mt-4">Welcome Back</h2>
                    <p class="text-sm opacity-70">Sign in to your account</p>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Email</span></label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="input input-bordered w-full @error('email') input-error @enderror"
                            placeholder="you@example.com" required>
                        @error('email')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control mt-4">
                        <label class="label"><span class="label-text font-medium">Password</span></label>
                        <input type="password" name="password"
                            class="input input-bordered w-full @error('password') input-error @enderror"
                            placeholder="••••••••" required>
                        @error('password')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                        <label class="label">
                            <a href="{{ route('password.request') }}" class="label-text-alt link link-hover">Forgot
                                password?</a>
                        </label>
                    </div>

                    <div class="form-control mt-6">
                        <button type="submit" class="btn btn-primary w-full">Login</button>
                    </div>
                </form>

                <div class="text-center text-xs opacity-50 mt-4">
                    Contact your company administrator for access
                </div>
            </div>
        </div>
    </div>
@endsection
