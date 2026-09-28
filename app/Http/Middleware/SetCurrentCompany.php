<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Support\CompanyContext;

class SetCurrentCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Explicit session company (company switcher) takes precedence.
        if (session()->has('current_company_id')) {
            $id = (int) session('current_company_id');
            $company = Company::withoutGlobalScopes()->whereKey($id)->first();
            if ($company) {
                CompanyContext::set($company->id);
                app()->instance('current_company', $company);
                View::share('currentCompany', $company);
                $request->merge(['company_id' => $company->id]);
                return $next($request);
            }
            session()->forget('current_company_id');
            CompanyContext::forget();
        }

        // 2. Authenticated user's default company.
        if (Auth::check() && Auth::user()->company_id) {
            CompanyContext::set((int) Auth::user()->company_id);
            $company = Company::withoutGlobalScopes()->whereKey(Auth::user()->company_id)->first();
            if ($company) {
                app()->instance('current_company', $company);
                View::share('currentCompany', $company);
                session(['current_company_id' => $company->id]);
                $request->merge(['company_id' => $company->id]);
                return $next($request);
            }
        }

        // 3. Subdomain fallback (legacy).
        $subdomain = $request->route('subdomain') ?? $request->getHost();

        if (in_array($subdomain, [config('app.domain', 'clusterfiy.test'), 'www'])) {
            View::share('currentCompany', null);
            return $next($request);
        }

        $company = Cache::remember("company_{$subdomain}", 3600, function () use ($subdomain) {
            return Company::withoutGlobalScopes()->where('subdomain', $subdomain)->where('is_active', true)->first();
        });

        if (! $company) {
            // No tenant resolved: continue without scope (public pages, auth).
            CompanyContext::forget();
            View::share('currentCompany', null);
            return $next($request);
        }

        CompanyContext::set($company->id);
        app()->instance('current_company', $company);
        View::share('currentCompany', $company);
        session(['current_company_id' => $company->id]);
        $request->merge(['company_id' => $company->id]);

        return $next($request);
    }
}
