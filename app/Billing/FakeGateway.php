<?php

namespace App\Billing;

use App\Models\Company;
use App\Models\User;

/**
 * Used when no Stripe keys are configured (local dev) and in tests that
 * exercise the checkout/portal flow without HTTP stubbing.
 */
class FakeGateway implements BillingGateway
{
    public function createCheckoutSession(Company $company, User $user, string $plan): array
    {
        $id = 'cs_test_' . $company->id . '_' . $plan;
        return ['id' => $id, 'url' => route('billing.success', ['company' => $company->id]) . '?session_id=' . $id];
    }

    public function createPortalSession(Company $company, string $returnUrl): array
    {
        return ['id' => 'bps_test_' . $company->id, 'url' => $returnUrl];
    }
}
