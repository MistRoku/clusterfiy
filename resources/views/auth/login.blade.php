@extends('layouts.guest')

@section('content')
    <div
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 p-4">
        <div class="card w-full max-w-md glass-auth shadow-2xl rounded-2xl">
            <div class="card-body p-8">
                <!-- Logo -->
                <div class="text-center mb-6">
                    <img src="{{ asset('images/logo-full.svg') }}" alt="Clusterfiy" class="h-12 mx-auto"
                        onerror="this.style.display='none'">
                    <h2 class="text-2xl font-bold mt-4 text-white">Welcome Back</h2>
                    <p class="text-sm text-white/70">Sign in to your account</p>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="form-control">
                        <label class="label"><span class="label-text text-white/80">Email</span></label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-white/40"></i>
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="input input-bordered w-full pl-10 bg-white/10 border-white/20 text-white placeholder-white/40 focus:border-white/60"
                                placeholder="you@example.com" required>
                        </div>
                        @error('email')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control mt-4">
                        <label class="label"><span class="label-text text-white/80">Password</span></label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-white/40"></i>
                            <input type="password" name="password"
                                class="input input-bordered w-full pl-10 bg-white/10 border-white/20 text-white placeholder-white/40 focus:border-white/60"
                                placeholder="••••••••" required>
                        </div>
                        @error('password')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                        <label class="label">
                            <a href="{{ route('password.request') }}"
                                class="label-text-alt link link-hover text-white/60 hover:text-white">Forgot password?</a>
                        </label>
                    </div>

                    <div class="form-control mt-6">
                        <button type="submit"
                            class="btn btn-primary w-full bg-white/20 hover:bg-white/30 border-white/30 text-white">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login
                        </button>
                    </div>
                </form>

                <div class="text-center text-xs text-white/50 mt-4">
                    Contact your company administrator for access
                </div>
            </div>
        </div>
    </div>
@endsection
