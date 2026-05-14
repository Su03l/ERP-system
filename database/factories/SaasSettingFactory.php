<?php

namespace Database\Factories;

use App\Models\SaasSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaasSetting>
 */
class SaasSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'default_trial_days' => 14,
            'default_currency' => 'SAR',
            'billing_enabled' => false,
            'marketplace_enabled' => true,
            'invoice_numbering_prefix' => 'SAAS-INV',
            'subscription_grace_period_days' => 7,
            'metadata' => [],
        ];
    }
}
