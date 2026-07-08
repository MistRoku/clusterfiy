<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            abort(403, 'Super Admin only.');
        }

        $user = Auth::user();

        // Support models that implement isSuperAdmin(), or fallback to common attributes
        $isSuper = false;
        if (method_exists($user, 'isSuperAdmin')) {
            // Use method_exists to satisfy static analysis tools that may not
            // recognize dynamic model methods.
            $isSuper = (bool) $user->isSuperAdmin();
        } elseif (isset($user->is_super_admin)) {
            $isSuper = (bool) $user->is_super_admin;
        } elseif (isset($user->role)) {
            $role = strtolower($user->role);
            $isSuper = in_array($role, ['superadmin', 'super_admin']);
        }

        if (! $isSuper) {
            abort(403, 'Super Admin only.');
        }
        return $next($request);
    }
}
