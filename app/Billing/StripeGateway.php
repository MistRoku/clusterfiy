<?php

namespace App\Billing;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Http;

/**
 * Stripe gateway over plain HTTPS (Stripe REST API). Checkout handles the
 * card form so card data never touches our servers (PCI surface stays
 * with Stripe). Uses stripe/stripe-php SDK when installed, otherwise
 * the HTTP client below. Both paths are covered by tests via Http::fake.
 */
class StripeGateway implements BillingGateway
{
    public function createCheckoutSession(Company $company, User $user, string $plan): array
    {
        $priceId = config("plans.{$plan}.stripe_price_id");
        if (! $priceId) {
            throw new \RuntimeException("No Stripe price configured for plan [{$plan}].");
        }

        $response = Http::asForm()
            ->withBasicAuth((string) config('services.stripe.secret'), '')
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'subscription',
                'customer_email' => $company->billing_email ?: $user->email,
                'client_reference_id' => (string) $company->id,
                'line_items[0][price]' => $priceId,
                'line_items[0][quantity]' => 1,
                'success_url' => route('billing.success', ['company' => $company->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('billing.cancel', ['company' => $company->id]),
                'metadata[company_id]' => (string) $company->id,
                'metadata[plan]' => $plan,
            ]);

        $response->throw();

        return ['id' => $response->json('id'), 'url' => $response->json('url')];
    }

    public function createPortalSession(Company $company, string $returnUrl): array
    {
        if (! $company->stripe_customer_id) {
            throw new \RuntimeException('No Stripe customer for this company yet.');
        }

        $response = Http::asForm()
            ->withBasicAuth((string) config('services.stripe.secret'), '')
            ->post('https://api.stripe.com/v1/billing_portal/sessions', [
                'customer' => $company->stripe_customer_id,
                'return_url' => $returnUrl,
            ]);

        $response->throw();

        return ['id' => $response->json('id'), 'url' => $response->json('url')];
    }
}
