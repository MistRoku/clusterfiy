<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\View;
use App\Models\Company;

class SetCurrentCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $request->route('subdomain') ?? $request->getHost();

        // Skip if it's the main domain or www
        if (in_array($subdomain, [config('app.domain', 'clusterfiy.test'), 'www'])) {
            View::share('currentCompany', null);
            session()->forget('current_company_id');
            return $next($request);
        }

        $company = Company::query()
            ->where('subdomain', $subdomain)
            ->where('is_active', true)
            ->first();

        if (!$company) {
            abort(404, 'Company not found.');
        }

        View::share('currentCompany', $company);
        session(['current_company_id' => $company->id]);

        return $next($request);
    }
}
