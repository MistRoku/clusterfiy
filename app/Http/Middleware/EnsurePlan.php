<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Support\CompanyContext;
use App\Models\Company;

class EnsurePlan
{
    public function handle(Request $request, Closure $next, string $plan): Response
    {
        $companyId = CompanyContext::get();
        if ($companyId) {
            $company = Company::withoutGlobalScopes()->find($companyId);
            if ($company && $company->plan !== $plan && ! ($plan === 'team' && $company->plan === 'team')) {
                // Free plan: block Excel exports.
                if ($request->wantsJson()) {
                    return response()->json(['message' => 'This feature requires the Team plan.'], 403);
                }
                return redirect()->route('pricing')->with('error', 'Excel exports require the Team plan.');
            }
        }
        return $next($request);
    }
}
