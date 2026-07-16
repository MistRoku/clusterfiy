@extends('layouts.guest')

@section('content')
    <div
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 p-4">
        <div class="card w-full max-w-md glass-auth shadow-2xl rounded-2xl">
            <div class="card-body p-8">
                <div class="text-center mb-6">
                    <img src="{{ asset('images/logo-icon.svg') }}" alt="Clusterfiy" class="h-16 mx-auto"
                        onerror="this.style.display='none'">
                    <h2 class="text-2xl font-bold mt-4 text-white">Set New Password</h2>
                    <p class="text-sm text-white/70">Choose a strong password</p>
                </div>

                <form method="POST" action="{{ route('password.store') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">
                    <input type="hidden" name="email" value="{{ $request->email }}">

                    <div class="form-control">
                        <label class="label"><span class="label-text text-white/80">New Password</span></label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-white/40"></i>
                            <input type="password" name="password" id="password"
                                class="input input-bordered w-full pl-10 bg-white/10 border-white/20 text-white placeholder-white/40 focus:border-white/60"
                                placeholder="••••••••" required>
                        </div>
                        @error('password')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control mt-4">
                        <label class="label"><span class="label-text text-white/80">Confirm Password</span></label>
                        <div class="relative">
                            <i class="fas fa-check-circle absolute left-3 top-1/2 -translate-y-1/2 text-white/40"></i>
                            <input type="password" name="password_confirmation"
                                class="input input-bordered w-full pl-10 bg-white/10 border-white/20 text-white placeholder-white/40 focus:border-white/60"
                                placeholder="••••••••" required>
                        </div>
                    </div>

                    <!-- Password strength meter -->
                    <div class="mt-2">
                        <div class="flex gap-1 h-1">
                            <div class="flex-1 rounded-full bg-white/20" id="reset-bar-1"></div>
                            <div class="flex-1 rounded-full bg-white/20" id="reset-bar-2"></div>
                            <div class="flex-1 rounded-full bg-white/20" id="reset-bar-3"></div>
                            <div class="flex-1 rounded-full bg-white/20" id="reset-bar-4"></div>
                        </div>
                        <p class="text-xs text-white/50 mt-1" id="reset-strength-text">Enter a password</p>
                    </div>

                    <div class="form-control mt-6">
                        <button type="submit"
                            class="btn btn-primary w-full bg-white/20 hover:bg-white/30 border-white/30 text-white">
                            <i class="fas fa-check mr-2"></i> Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const passwordInput = document.getElementById('password');
                const bars = [
                    document.getElementById('reset-bar-1'),
                    document.getElementById('reset-bar-2'),
                    document.getElementById('reset-bar-3'),
                    document.getElementById('reset-bar-4')
                ];
                const text = document.getElementById('reset-strength-text');

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
