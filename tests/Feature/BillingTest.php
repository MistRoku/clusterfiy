<?php

namespace Tests\Feature;

use App\Billing\PlanLimits;
use App\Billing\StripeGateway;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\Task;
use App\Models\User;
use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['super_admin', 'company_admin', 'manager', 'employee'] as $r) {
            Role::firstOrCreate(['name' => $r]);
        }
        config()->set('services.stripe.webhook_secret', 'whsec_test');
        config()->set('services.stripe.secret', null);
        config()->set('plans.team.stripe_price_id', 'price_team_test');
    }

    protected function ownerCompany(string $plan = 'free', int $members = 0): array
    {
        $creator = User::factory()->create();
        $company = Company::create([
            'name' => 'Acme ' . uniqid(),
            'subdomain' => 'acme' . rand(100000, 999999),
            'is_active' => true,
            'plan' => $plan,
            'subscription_status' => $plan === 'team' ? 'active' : 'free',
            'created_by' => $creator->id,
        ]);
        $owner = User::factory()->create(['company_id' => $company->id]);
        $owner->assignRole('company_admin');
        for ($i = 0; $i < $members; $i++) {
            User::factory()->create(['company_id' => $company->id]);
        }
        return [$company->fresh(), $owner];
    }

    protected function sign(array $payload): array
    {
        $body = json_encode($payload);
        $t = time();
        $sig = hash_hmac('sha256', $t . '.' . $body, 'whsec_test');
        return [$body, "t={$t},v1={$sig}"];
    }

    protected function stripeEvent(string $id, string $type, array $object): array
    {
        return ['id' => $id, 'type' => $type, 'data' => ['object' => $object]];
    }

    // --- Checkout ---

    public function test_checkout_creates_session_and_redirects_without_changing_plan_yet(): void
    {
        [$company, $owner] = $this->ownerCompany('free');

        $response = $this->actingAs($owner)->post('/billing/checkout', ['plan' => 'team']);

        $response->assertStatus(303);
        $this->assertStringContainsString('cs_test_', $response->headers->get('Location'));
        // Plan flips only when the webhook confirms payment.
        $this->assertEquals('free', $company->fresh()->plan);
    }

    public function test_stripe_gateway_posts_correct_checkout_payload(): void
    {
        config()->set('services.stripe.secret', 'sk_test_123');
        Http::fake(['api.stripe.com/*' => Http::response(['id' => 'cs_123', 'url' => 'https://checkout.stripe.com/pay/cs_123'], 200)]);

        [$company, $owner] = $this->ownerCompany('free');
        $session = (new StripeGateway())->createCheckoutSession($company, $owner, 'team');

        $this->assertEquals('cs_123', $session['id']);
        Http::assertSent(function ($request) use ($company) {
            return str_contains($request->url(), 'checkout/sessions')
                && $request['mode'] === 'subscription'
                && $request['metadata[company_id]'] === (string) $company->id
                && $request['metadata[plan]'] === 'team';
        });
    }

    public function test_portal_redirects_to_stripe(): void
    {
        config()->set('services.stripe.secret', 'sk_test_123');
        Http::fake(['api.stripe.com/*' => Http::response(['id' => 'bps_123', 'url' => 'https://billing.stripe.com/session/bps_123'], 200)]);

        [$company, $owner] = $this->ownerCompany('team');
        $company->forceFill(['stripe_customer_id' => 'cus_123'])->save();

        $response = $this->actingAs($owner)->post('/billing/portal');

        $response->assertStatus(303);
        $this->assertStringContainsString('billing.stripe.com', $response->headers->get('Location'));
    }

    // --- Webhooks ---

    public function test_webhook_checkout_completed_activates_team(): void
    {
        [$company] = $this->ownerCompany('free');

        $event = $this->stripeEvent('evt_1', 'checkout.session.completed', [
            'client_reference_id' => (string) $company->id,
            'metadata' => ['company_id' => (string) $company->id, 'plan' => 'team'],
            'subscription' => 'sub_123',
            'customer' => 'cus_123',
        ]);
        [$body, $sig] = $this->sign($event);

        $this->postJson('/stripe/webhook', $event, ['Stripe-Signature' => $sig])
            ->assertOk()
            ->assertJson(['received' => true]);

        $company = $company->fresh();
        $this->assertEquals('team', $company->plan);
        $this->assertEquals('active', $company->subscription_status);
        $this->assertEquals('sub_123', $company->stripe_subscription_id);
        $this->assertTrue(PlanLimits::canExport($company));
        $this->assertEquals(1, Subscription::where('company_id', $company->id)->count());
    }

    public function test_webhook_duplicate_delivery_is_processed_once(): void
    {
        [$company] = $this->ownerCompany('free');

        $event = $this->stripeEvent('evt_dup', 'checkout.session.completed', [
            'client_reference_id' => (string) $company->id,
            'metadata' => ['company_id' => (string) $company->id, 'plan' => 'team'],
            'subscription' => 'sub_dup',
            'customer' => 'cus_dup',
        ]);
        [$body, $sig] = $this->sign($event);

        // Same event body twice: second delivery must not double-apply.
        $this->postJson('/stripe/webhook', $event, ['Stripe-Signature' => $sig])->assertOk();
        // Re-sign with a fresh timestamp so the replay guard passes; id must still dedupe.
        [$body2, $sig2] = $this->sign($event);
        $second = $this->postJson('/stripe/webhook', $event, ['Stripe-Signature' => $sig2]);

        $second->assertOk()->assertJson(['duplicate' => true]);
        $this->assertEquals(1, WebhookEvent::where('event_id', 'evt_dup')->count());
        $this->assertEquals(1, Subscription::where('company_id', $company->id)->count());
        $this->assertEquals('team', $company->fresh()->plan);
    }

    public function test_webhook_invalid_signature_is_rejected(): void
    {
        [$company] = $this->ownerCompany('free');

        $event = $this->stripeEvent('evt_bad', 'checkout.session.completed', [
            'client_reference_id' => (string) $company->id,
            'metadata' => ['company_id' => (string) $company->id, 'plan' => 'team'],
        ]);

        $this->postJson('/stripe/webhook', $event)
            ->assertStatus(400);

        $this->assertEquals('free', $company->fresh()->plan);
        $this->assertEquals(0, WebhookEvent::where('event_id', 'evt_bad')->count());
    }

    public function test_payment_failed_sets_past_due_with_grace_and_keeps_features(): void
    {
        [$company] = $this->ownerCompany('team');
        $company->forceFill(['stripe_customer_id' => 'cus_9', 'stripe_subscription_id' => 'sub_9'])->save();
        Subscription::create(['company_id' => $company->id, 'stripe_subscription_id' => 'sub_9', 'stripe_customer_id' => 'cus_9', 'plan' => 'team', 'status' => 'active']);

        $event = $this->stripeEvent('evt_fail', 'invoice.payment_failed', [
            'subscription' => 'sub_9', 'customer' => 'cus_9',
        ]);
        [$body, $sig] = $this->sign($event);

        $this->postJson('/stripe/webhook', $event, ['Stripe-Signature' => $sig])->assertOk();

        $company = $company->fresh();
        $this->assertEquals('past_due', $company->subscription_status);
        $this->assertNotNull($company->grace_until);
        $this->assertTrue($company->teamFeaturesActive());
    }

    public function test_subscription_deleted_downgrades_but_keeps_data(): void
    {
        [$company, $owner] = $this->ownerCompany('team', 5);
        $company->forceFill(['stripe_subscription_id' => 'sub_gone'])->save();
        $task = Task::withoutGlobalScopes()->create([
            'company_id' => $company->id, 'title' => 'Keep me', 'status' => 'todo', 'priority' => 'low', 'created_by' => $owner->id,
        ]);
        Subscription::create(['company_id' => $company->id, 'stripe_subscription_id' => 'sub_gone', 'plan' => 'team', 'status' => 'active']);

        $event = $this->stripeEvent('evt_del', 'customer.subscription.deleted', ['id' => 'sub_gone', 'customer' => 'cus_x']);
        [$body, $sig] = $this->sign($event);

        $this->postJson('/stripe/webhook', $event, ['Stripe-Signature' => $sig])->assertOk();

        $company = $company->fresh();
        $this->assertEquals('free', $company->plan);
        $this->assertEquals('canceled', $company->subscription_status);
        // Data is kept...
        $this->assertNotNull(Task::withoutGlobalScopes()->find($task->id));
        // ...but adds are blocked until compliant (6 members > 5 limit).
        $this->assertTrue(PlanLimits::isOverLimit($company));
        $this->assertFalse(PlanLimits::canAddMember($company));
    }

    // --- Limit enforcement ---

    public function test_free_member_limit_blocks_sixth_invite(): void
    {
        [$company, $owner] = $this->ownerCompany('free', 4); // owner + 4 = 5
        $this->assertEquals(5, $company->fresh()->memberCount());

        $response = $this->actingAs($owner)->from('/invitations')->post('/invitations', [
            'email' => 'sixth@example.com', 'role' => 'member',
        ]);

        $response->assertRedirect('/invitations');
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('company_invitations', ['email' => 'sixth@example.com']);
    }

    public function test_free_company_limit_blocks_second_company(): void
    {
        [$company, $owner] = $this->ownerCompany('free');

        $response = $this->actingAs($owner)->post('/companies', [
            'name' => 'Second Co', 'subdomain' => 'second' . rand(100000, 999999),
        ]);

        $response->assertRedirect(route('pricing'));
        $this->assertDatabaseMissing('companies', ['name' => 'Second Co']);
    }

    public function test_team_plan_allows_unlimited_members_and_exports(): void
    {
        [$company] = $this->ownerCompany('team', 8);

        $this->assertNull(PlanLimits::memberLimit($company));
        $this->assertTrue(PlanLimits::canAddMember($company));
        $this->assertTrue(PlanLimits::canExport($company));
    }

    public function test_free_plan_blocks_exports(): void
    {
        [$company, $owner] = $this->ownerCompany('free');

        $this->assertFalse(PlanLimits::canExport($company));
        $this->actingAs($owner)->get('/reports/export')->assertRedirect(route('billing.show'));
    }

    public function test_over_limit_company_keeps_read_access_but_cannot_add(): void
    {
        [$company, $owner] = $this->ownerCompany('free', 5); // owner + 5 = 6 > 5
        $task = Task::withoutGlobalScopes()->create([
            'company_id' => $company->id, 'title' => 'Readable task', 'status' => 'todo', 'priority' => 'low', 'created_by' => $owner->id,
        ]);

        // Reads still work.
        $this->actingAs($owner)->get('/tasks')->assertOk()->assertSee('Readable task');

        // Adds are blocked with a compliance message.
        $response = $this->actingAs($owner)->from('/invitations')->post('/invitations', [
            'email' => 'extra@example.com', 'role' => 'member',
        ]);
        $response->assertRedirect('/invitations');
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'over the Free plan limit',
            PlanLimits::addMemberBlockedMessage($company->fresh())
        );
    }
}
