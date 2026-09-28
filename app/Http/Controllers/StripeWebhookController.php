<?php

namespace App\Http\Controllers;

use App\Models\WebhookEvent;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Stripe webhooks with idempotency: every Stripe event_id is stored on
 * first sight and marked processed after handling, so duplicate
 * deliveries (Stripe retries) return 200 without double-applying.
 */
class StripeWebhookController extends Controller
{
    public function handle(Request $request, SubscriptionService $subscriptions)
    {
        $payload = $request->getContent();
        $signature = (string) $request->header('Stripe-Signature', '');
        $secret = (string) config('services.stripe.webhook_secret');

        if (! $this->validSignature($payload, $signature, $secret)) {
            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        $event = json_decode($payload, true);
        if (! is_array($event) || empty($event['id']) || empty($event['type'])) {
            return response()->json(['message' => 'Malformed event.'], 400);
        }

        $record = DB::transaction(function () use ($event) {
            return WebhookEvent::lockForUpdate()->firstOrCreate(
                ['event_id' => $event['id']],
                ['type' => $event['type'], 'payload' => $event]
            );
        });

        if ($record->isProcessed()) {
            return response()->json(['received' => true, 'duplicate' => true]);
        }

        try {
            match ($event['type']) {
                'checkout.session.completed' => $subscriptions->handleCheckoutCompleted($event),
                'customer.subscription.created',
                'customer.subscription.updated' => $subscriptions->handleSubscriptionChanged($event),
                'customer.subscription.deleted' => $subscriptions->handleSubscriptionDeleted($event),
                'invoice.payment_failed' => $subscriptions->handlePaymentFailed($event),
                default => Log::info('Stripe webhook ignored', ['type' => $event['type']]),
            };
        } catch (\Throwable $e) {
            Log::error('Stripe webhook handling failed', ['event' => $event['id'], 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Handler error, will retry.'], 500);
        }

        $record->forceFill(['processed_at' => now()])->save();

        return response()->json(['received' => true]);
    }

    public function validSignature(string $payload, string $header, string $secret): bool
    {
        if ($secret === '' || $header === '') {
            return false;
        }
        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $header) as $part) {
            [$k, $v] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($k === 't') {
                $timestamp = $timestamp ?? $v;
            } elseif ($k === 'v1' && $v) {
                $signatures[] = $v;
            }
        }
        if (! $timestamp || ! $signatures) {
            return false;
        }
        // Reject replays older than 5 minutes.
        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }
        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        foreach ($signatures as $sig) {
            if (hash_equals($expected, $sig)) {
                return true;
            }
        }
        return false;
    }
}
