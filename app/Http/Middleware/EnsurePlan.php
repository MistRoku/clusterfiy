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
        if ($companyId && $plan === 'team') {
            $company = Company::withoutGlobalScopes()->find($companyId);
            if ($company && ! \App\Billing\PlanLimits::canExport($company)) {
                if ($request->wantsJson()) {
                    return response()->json(['message' => 'This feature requires the Team plan.'], 403);
                }
                return redirect()->route('billing.show')->with('error', 'Excel exports require the Team plan. Upgrade to unlock exports.');
            }
        }
        return $next($request);
    }
}
