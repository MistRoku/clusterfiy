<?php

namespace App\Http\Controllers;

use App\Billing\BillingGateway;
use App\Billing\PlanLimits;
use App\Models\Company;
use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function show()
    {
        $company = Company::withoutGlobalScopes()->findOrFail(CompanyContext::get());
        $this->authorize('manageBilling', $company);

        return view('billing.show', [
            'title' => 'Billing: ' . $company->name,
            'metaDescription' => 'Manage the Clusterfiy subscription for ' . $company->name . '.',
            'company' => $company,
            'memberCount' => $company->memberCount(),
            'memberLimit' => PlanLimits::memberLimit($company),
            'overLimitBy' => PlanLimits::memberDeficit($company),
            'stripeKey' => config('services.stripe.key'),
        ]);
    }

    public function checkout(Request $request, BillingGateway $billing)
    {
        $company = Company::withoutGlobalScopes()->findOrFail(CompanyContext::get());
        $this->authorize('manageBilling', $company);

        $plan = $request->validate(['plan' => 'required|in:team'])['plan'];

        $session = $billing->createCheckoutSession($company, Auth::user(), $plan);

        return redirect()->away($session['url'], 303);
    }

    public function portal(Request $request, BillingGateway $billing)
    {
        $company = Company::withoutGlobalScopes()->findOrFail(CompanyContext::get());
        $this->authorize('manageBilling', $company);

        try {
            $session = $billing->createPortalSession($company, route('billing.show'));
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Billing portal is not available yet: ' . $e->getMessage());
        }

        return redirect()->away($session['url'], 303);
    }

    public function success(Request $request)
    {
        $company = Company::withoutGlobalScopes()->findOrFail($request->query('company') ?? CompanyContext::get());

        return view('billing.success', [
            'title' => 'Subscription confirmed',
            'metaDescription' => 'Your Clusterfiy Team subscription is being activated.',
            'company' => $company,
        ]);
    }

    public function cancel(Request $request)
    {
        $company = Company::withoutGlobalScopes()->findOrFail($request->query('company') ?? CompanyContext::get());

        return view('billing.cancel', [
            'title' => 'Checkout cancelled',
            'metaDescription' => 'Stripe Checkout was cancelled. No charge was made.',
            'company' => $company,
        ]);
    }
}
