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

        if (in_array($subdomain, [config('app.domain', 'clusterfiy.test'), 'www'])) {
            return $next($request);
        }

        $company = Company::where('subdomain', $subdomain)->where('is_active', true)->first();

        if (!$company) {
            abort(404, 'Company not found.');
        }

        View::share('currentCompany', $company);
        session(['current_company_id' => $company->id]);

        return $next($request);
    }
}
