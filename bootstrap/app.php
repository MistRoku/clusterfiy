<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Support\Facades\Log;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\SetCurrentCompany::class,
            \App\Http\Middleware\LogLogin::class,
        ]);
        $middleware->alias([
            'is_super_admin' => \App\Http\Middleware\IsSuperAdmin::class,
            'throttle.login' => \Illuminate\Routing\Middleware\ThrottleRequests::class . ':5,1',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, Request $request) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Resource not found'], 404);
            }
        });
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        });
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
        });
    })
    ->create();

RateLimiter::for('api', function ($job) {
    return Limit::perMinute(60)->by($job->user()?->id ?: $job->ip());
});

RateLimiter::for('login', function ($job) {
    return Limit::perMinute(5)->by($job->input('email') . $job->ip());
});

RateLimiter::for('password-reset', function ($job) {
    return Limit::perMinute(3)->by($job->input('email') . $job->ip());
});
