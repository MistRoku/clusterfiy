<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\LoginHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
       $response = $next($request);

        if (Auth::check() && $request->isMethod('post') && $request->routeIs('login')) {
            $userAgent = $request->userAgent();
            $device = $this->parseDevice($userAgent);
            LoginHistory::create([
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $userAgent,
                'device' => $device,
                'successful' => true,
                'login_at' => now(),
            ]);

            $user = Auth::user();
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
                'last_login_device' => $device,
            ]);
        }

        return $response;
    }

    /**
     * Log successful login.
     */
    private function logSuccessfulLogin(Request $request): void
    {
        $user = Auth::user();

        try {
            LoginHistory::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_at' => now(),
                'status' => 'success',
                'location' => $this->getLocationFromIp($request->ip()),
            ]);

            // Update user's last login
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log login history', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * Log failed login attempt.
     */
    private function logFailedLogin(Request $request): void
    {
        try {
            LoginHistory::create([
                'user_id' => null,
                'email_attempted' => $request->input('email'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_at' => now(),
                'status' => 'failed',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log failed login', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get approximate location from IP.
     */
    private function getLocationFromIp(?string $ip): ?string
    {
        if (! $ip || $ip === '127.0.0.1') {
            return 'Local';
        }

        // In production, integrate with a GeoIP service
        return null;
    }
    private function parseDevice($userAgent)
    {
        if (str_contains($userAgent, 'Windows')) return 'Windows';
        if (str_contains($userAgent, 'Mac')) return 'Mac';
        if (str_contains($userAgent, 'Linux')) return 'Linux';
        if (str_contains($userAgent, 'Android')) return 'Android';
        if (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) return 'iOS';
        return 'Unknown';
    }

}
