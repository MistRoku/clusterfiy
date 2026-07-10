<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()
    {
        // If user is already logged in, redirect them properly
        if (Auth::check()) {
            $user = Auth::user();

            // Super Admin → company management
            if ($user->isSuperAdmin()) {
                return redirect()->route('companies.index');
            }

            // Regular user with company → tenant dashboard
            if ($user->company) {
                return redirect()->route('tenant.dashboard', ['subdomain' => $user->company->subdomain]);
            }

            // Fallback: no company assigned
            abort(403, 'You are not assigned to any company.');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return redirect()->intended(route('companies.index'));
        }

        if ($user->company) {
            return redirect()->intended(route('tenant.dashboard', ['subdomain' => $user->company->subdomain]));
        }

        // Fallback
        return redirect()->intended('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
