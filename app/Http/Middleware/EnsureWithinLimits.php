<?php

namespace App\Http\Middleware;

use App\Billing\PlanLimits;
use App\Models\Company;
use App\Support\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Graceful-downgrade gate: over-limit (or past-due) companies keep full
 * read access to their data but member-adds and company-creates are
 * blocked until compliant or re-upgraded.
 */
class EnsureWithinLimits
{
    public function handle(Request $request, Closure $next, string $action): Response
    {
        $companyId = CompanyContext::get();

        if ($action === 'add-member' && $companyId) {
            $company = Company::withoutGlobalScopes()->find($companyId);
            if ($company && ! PlanLimits::canAddMember($company)) {
                $message = PlanLimits::addMemberBlockedMessage($company);
                if ($request->wantsJson()) {
                    return response()->json(['message' => $message], 403);
                }
                return back()->withErrors(['email' => $message])->withInput();
            }
        }

        if ($action === 'create-company') {
            $user = $request->user();
            if ($user && ! PlanLimits::canCreateCompany($user)) {
                $message = 'The Free plan allows 1 company. Upgrade to Team for unlimited companies.';
                if ($request->wantsJson()) {
                    return response()->json(['message' => $message], 403);
                }
                return redirect()->route('pricing')->with('error', $message);
            }
        }

        return $next($request);
    }
}
