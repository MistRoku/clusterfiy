<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

class EnforceBillingGrace extends Command
{
    protected $signature = 'billing:enforce-grace';

    protected $description = 'Downgrade past-due companies whose payment grace window has expired (data is kept)';

    public function handle(): int
    {
        $companies = Company::withoutGlobalScopes()
            ->where('subscription_status', 'past_due')
            ->whereNotNull('grace_until')
            ->where('grace_until', '<', now())
            ->get();

        foreach ($companies as $company) {
            $company->forceFill([
                'plan' => 'free',
                'subscription_status' => 'canceled',
                'grace_until' => null,
            ])->save();
            $company->subscriptions()->update(['plan' => 'free', 'status' => 'canceled']);
            $this->info("Downgraded company #{$company->id} ({$company->name}) to free; data kept.");
        }

        $this->info("Enforced grace for {$companies->count()} companie(s).");

        return self::SUCCESS;
    }
}
