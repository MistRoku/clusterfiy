<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')->nullable()->after('plan');
            }
            if (! Schema::hasColumn('companies', 'stripe_subscription_id')) {
                $table->string('stripe_subscription_id')->nullable()->after('stripe_customer_id');
            }
            if (! Schema::hasColumn('companies', 'subscription_status')) {
                $table->string('subscription_status')->default('free')->after('stripe_subscription_id');
            }
            if (! Schema::hasColumn('companies', 'billing_email')) {
                $table->string('billing_email')->nullable()->after('subscription_status');
            }
            if (! Schema::hasColumn('companies', 'grace_until')) {
                $table->timestamp('grace_until')->nullable()->after('billing_email');
            }
        });

        if (! Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('stripe_subscription_id')->nullable()->unique();
                $table->string('stripe_customer_id')->nullable();
                $table->string('plan')->default('free');
                $table->string('status')->default('free');
                $table->timestamp('current_period_end')->nullable();
                $table->boolean('cancel_at_period_end')->default(false);
                $table->timestamps();
                $table->index('company_id');
            });
        }

        // Idempotency log for Stripe webhooks: processed event IDs so
        // retries never double-apply (same thinking as an idempotent outbox).
        if (! Schema::hasTable('webhook_events')) {
            Schema::create('webhook_events', function (Blueprint $table) {
                $table->id();
                $table->string('event_id')->unique();
                $table->string('type');
                $table->json('payload')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('subscriptions');

        foreach (['stripe_customer_id', 'stripe_subscription_id', 'subscription_status', 'billing_email', 'grace_until'] as $col) {
            if (Schema::hasColumn('companies', $col)) {
                Schema::table('companies', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }
    }
};
