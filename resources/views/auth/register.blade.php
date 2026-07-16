@extends('layouts.guest')

@section('content')
    <div
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 p-4">
        <div class="card w-full max-w-md glass-auth shadow-2xl rounded-2xl">
            <div class="card-body p-8">
                <div class="text-center mb-6">
                    <img src="{{ asset('images/logo-full.svg') }}" alt="Clusterfiy" class="h-12 mx-auto"
                        onerror="this.style.display='none'">
                    <h2 class="text-2xl font-bold mt-4 text-white">Create Account</h2>
                    <p class="text-sm text-white/70">Start your journey with Clusterfiy</p>
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="form-control">
                        <label class="label"><span class="label-text text-white/80">Full Name</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="input input-bordered w-full bg-white/10 border-white/20 text-white placeholder-white/40"
                            placeholder="John Doe" required>
                        @error('name')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control mt-3">
                        <label class="label"><span class="label-text text-white/80">Email</span></label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="input input-bordered w-full bg-white/10 border-white/20 text-white placeholder-white/40"
                            placeholder="you@example.com" required>
                        @error('email')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control mt-3">
                        <label class="label"><span class="label-text text-white/80">Password</span></label>
                        <input type="password" name="password" id="password"
                            class="input input-bordered w-full bg-white/10 border-white/20 text-white placeholder-white/40"
                            placeholder="••••••••" required>
                        @error('password')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control mt-3">
                        <label class="label"><span class="label-text text-white/80">Confirm Password</span></label>
                        <input type="password" name="password_confirmation"
                            class="input input-bordered w-full bg-white/10 border-white/20 text-white placeholder-white/40"
                            placeholder="••••••••" required>
                    </div>

                    <!-- Password Strength Meter -->
                    <div class="mt-2" x-data="{ password: '' }">
                        <input type="hidden" x-model="password" id="password-strength-input">
                        <div class="flex gap-1 h-1">
                            <div class="flex-1 rounded-full bg-white/20" id="strength-bar-1"></div>
                            <div class="flex-1 rounded-full bg-white/20" id="strength-bar-2"></div>
                            <div class="flex-1 rounded-full bg-white/20" id="strength-bar-3"></div>
                            <div class="flex-1 rounded-full bg-white/20" id="strength-bar-4"></div>
                        </div>
                        <p class="text-xs text-white/50 mt-1" id="strength-text">Enter a password</p>
                    </div>

                    <div class="form-control mt-3">
                        <label class="label"><span class="label-text text-white/80">Company Name</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}"
                            class="input input-bordered w-full bg-white/10 border-white/20 text-white placeholder-white/40"
                            placeholder="Acme Corp" required>
                        @error('company_name')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control mt-3">
                        <label class="label"><span class="label-text text-white/80">Subdomain</span></label>
                        <input type="text" name="company_subdomain" value="{{ old('company_subdomain') }}"
                            class="input input-bordered w-full bg-white/10 border-white/20 text-white placeholder-white/40"
                            placeholder="acme" required>
                        <span class="text-xs text-white/40 mt-1">.clusterfiy.com</span>
                        @error('company_subdomain')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control mt-6">
                        <button type="submit"
                            class="btn btn-primary w-full bg-white/20 hover:bg-white/30 border-white/30 text-white">
                            <i class="fas fa-user-plus mr-2"></i> Register
                        </button>
                    </div>
                </form>

                <div class="text-center text-sm mt-4">
                    <span class="text-white/60">Already have an account?</span>
                    <a href="{{ route('login') }}" class="link link-hover text-white hover:text-white/80">Login</a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const passwordInput = document.getElementById('password');
                const bars = [
                    document.getElementById('strength-bar-1'),
                    document.getElementById('strength-bar-2'),
                    document.getElementById('strength-bar-3'),
                    document.getElementById('strength-bar-4')
                ];
                const text = document.getElementById('strength-text');

                passwordInput.addEventListener('input', function() {
                    const val = this.value;
                    const strength = getPasswordStrength(val);
                    const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
                    const labels = ['Weak', 'Fair', 'Good', 'Strong'];

                    bars.forEach((bar, i) => {
                        bar.className = 'flex-1 rounded-full transition-all duration-300';
                        if (i < strength) {
                            bar.classList.add(colors[strength - 1]);
                        } else {
                            bar.classList.add('bg-white/20');
                        }
                    });
                    text.textContent = val.length > 0 ? labels[strength - 1] : 'Enter a password';
                    text.className = 'text-xs mt-1 ' + (strength > 0 ? colors[strength - 1].replace('bg-',
                        'text-') : 'text-white/50');
                });

                function getPasswordStrength(password) {
                    let score = 0;
                    if (password.length >= 8) score++;
                    if (password.length >= 12) score++;
                    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
                    if (/\d/.test(password) && /[^a-zA-Z0-9]/.test(password)) score++;
                    return Math.min(score, 4);
                }
            });
        </script>
    @endpush
@endsection
