<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityLog;

class IsSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!Auth::user()?->isSuperAdmin()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'event' => 'unauthorized_access',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_values' => ['route' => $request->route()?->getName()],
                'new_values' => ['reason' => 'super_admin_required'],
            ]);
            abort($request->wantsJson() ? 403 : 403, 'Super admin only.');
        }

        // Log super admin access for audit
        \Illuminate\Support\Facades\Log::info('Super admin access', [
            'user_id' => $user->id,
            'email' => $user->email,
            'route' => $request->route()?->getName(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $next($request);
    }
}
