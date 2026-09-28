<?php

namespace App\Billing;

use App\Models\Company;
use App\Models\User;

interface BillingGateway
{
    /** @return array{id: string, url: string} */
    public function createCheckoutSession(Company $company, User $user, string $plan): array;

    /** @return array{id: string, url: string} */
    public function createPortalSession(Company $company, string $returnUrl): array;
}
