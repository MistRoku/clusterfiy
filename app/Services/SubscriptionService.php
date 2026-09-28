<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

/**
 * Applies Stripe webhook events to local billing state. Every handler is
 * idempotent by construction (upserts keyed on Stripe IDs), and the
 * caller guarantees each event_id is processed at most once.
 */
class SubscriptionService
{
    public function handleCheckoutCompleted(array $event): void
    {
        $session = $event['data']['object'] ?? [];
        $companyId = $session['metadata']['company_id'] ?? $session['client_reference_id'] ?? null;
        if (! $companyId) {
            return;
        }

        DB::transaction(function () use ($companyId, $session, $event) {
            $company = Company::withoutGlobalScopes()->lockForUpdate()->find($companyId);
            if (! $company) {
                return;
            }

            $plan = $session['metadata']['plan'] ?? 'team';
            $subscriptionId = $session['subscription'] ?? null;
            $customerId = is_array($session['customer'] ?? null)
                ? ($session['customer']['id'] ?? null)
                : ($session['customer'] ?? null);

            $company->forceFill([
                'plan' => $plan,
                'subscription_status' => 'active',
                'stripe_customer_id' => $customerId ?: $company->stripe_customer_id,
                'stripe_subscription_id' => $subscriptionId ?: $company->stripe_subscription_id,
                'grace_until' => null,
            ])->save();

            Subscription::updateOrCreate(
                ['company_id' => $company->id],
                [
                    'stripe_subscription_id' => $subscriptionId,
                    'stripe_customer_id' => $customerId ?: $company->stripe_customer_id,
                    'plan' => $plan,
                    'status' => 'active',
                    'cancel_at_period_end' => false,
                ]
            );
        });
    }

    public function handleSubscriptionChanged(array $event): void
    {
        $object = $event['data']['object'] ?? [];
        $subscriptionId = $object['id'] ?? null;
        if (! $subscriptionId) {
            return;
        }

        DB::transaction(function () use ($object, $subscriptionId) {
            $subscription = Subscription::where('stripe_subscription_id', $subscriptionId)->lockForUpdate()->first();
            $company = $subscription?->company
                ?? Company::withoutGlobalScopes()->where('stripe_subscription_id', $subscriptionId)->lockForUpdate()->first();
            if (! $company) {
                return;
            }

            $status = $object['status'] ?? 'active';
            $cancelAtPeriodEnd = (bool) ($object['cancel_at_period_end'] ?? false);
            $periodEnd = isset($object['current_period_end'])
                ? now()->setTimestamp((int) $object['current_period_end'])
                : null;

            // scheduled cancel: keep team until period end, then treat as canceled
            $plan = $company->plan;
            $localStatus = $status;
            if ($status === 'canceled' || ($cancelAtPeriodEnd && $periodEnd && $periodEnd->isPast())) {
                $plan = 'free';
                $localStatus = 'canceled';
            }

            $company->forceFill([
                'plan' => $plan,
                'subscription_status' => $localStatus,
                'stripe_subscription_id' => $subscriptionId,
                'stripe_customer_id' => is_string($object['customer'] ?? null) ? $object['customer'] : $company->stripe_customer_id,
                'grace_until' => $localStatus === 'canceled' ? null : $company->grace_until,
            ])->save();

            Subscription::updateOrCreate(
                ['company_id' => $company->id],
                [
                    'stripe_subscription_id' => $subscriptionId,
                    'stripe_customer_id' => $company->stripe_customer_id,
                    'plan' => $plan,
                    'status' => $localStatus,
                    'current_period_end' => $periodEnd,
                    'cancel_at_period_end' => $cancelAtPeriodEnd,
                ]
            );
        });
    }

    public function handleSubscriptionDeleted(array $event): void
    {
        $object = $event['data']['object'] ?? [];
        $subscriptionId = $object['id'] ?? null;
        if (! $subscriptionId) {
            return;
        }

        DB::transaction(function () use ($subscriptionId) {
            $subscription = Subscription::where('stripe_subscription_id', $subscriptionId)->lockForUpdate()->first();
            $company = $subscription?->company
                ?? Company::withoutGlobalScopes()->where('stripe_subscription_id', $subscriptionId)->lockForUpdate()->first();
            if (! $company) {
                return;
            }

            // Graceful downgrade: data is kept, plan flips to free.
            // Adds stay blocked until member count is compliant (see PlanLimits).
            $company->forceFill([
                'plan' => 'free',
                'subscription_status' => 'canceled',
                'grace_until' => null,
            ])->save();

            if ($subscription) {
                $subscription->forceFill(['plan' => 'free', 'status' => 'canceled'])->save();
            }
        });
    }

    public function handlePaymentFailed(array $event): void
    {
        $object = $event['data']['object'] ?? [];
        $subscriptionId = $object['subscription'] ?? ($object['id'] ?? null);
        $customerId = is_string($object['customer'] ?? null) ? $object['customer'] : null;

        DB::transaction(function () use ($subscriptionId, $customerId) {
            $subscription = $subscriptionId
                ? Subscription::where('stripe_subscription_id', $subscriptionId)->lockForUpdate()->first()
                : null;
            $company = $subscription?->company
                ?? ($subscriptionId
                    ? Company::withoutGlobalScopes()->where('stripe_subscription_id', $subscriptionId)->lockForUpdate()->first()
                    : null)
                ?? ($customerId
                    ? Company::withoutGlobalScopes()->where('stripe_customer_id', $customerId)->lockForUpdate()->first()
                    : null);
            if (! $company) {
                return;
            }

            // 7-day grace: team features keep working, banner warns. After
            // that the subscription sync (or delete) flips the plan to free.
            $company->forceFill([
                'subscription_status' => 'past_due',
                'grace_until' => now()->addDays(7),
            ])->save();

            if ($subscription) {
                $subscription->forceFill(['status' => 'past_due'])->save();
            }
        });
    }
}
