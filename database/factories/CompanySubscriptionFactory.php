<?php

namespace Database\Factories;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanySubscription>
 */
class CompanySubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'plan_id' => Plan::factory(),
            'status' => SubscriptionStatus::Trialing,
            'billing_cycle' => BillingCycle::Monthly,
            'starts_at' => now(),
            'ends_at' => null,
            'trial_ends_at' => now()->addDays(14),
            'cancelled_at' => null,
            'grace_ends_at' => null,
            'metadata' => [],
        ];
    }
}
