<?php

namespace Database\Factories;

use App\Enums\SubscriptionInvoiceStatus;
use App\Models\CompanySubscription;
use App\Models\SubscriptionInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionInvoice>
 */
class SubscriptionInvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subscription = CompanySubscription::factory()->create();

        return [
            'company_id' => $subscription->company_id,
            'subscription_id' => $subscription->id,
            'invoice_number' => fake()->unique()->bothify('SUB-INV-####'),
            'invoice_date' => fake()->date(),
            'due_date' => fake()->optional()->date(),
            'status' => SubscriptionInvoiceStatus::Draft,
            'subtotal' => 100,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 100,
            'paid_amount' => 0,
            'balance_due' => 100,
            'currency' => 'SAR',
            'metadata' => [],
        ];
    }

    public function forSubscription(CompanySubscription $subscription): static
    {
        return $this->state(fn (): array => [
            'company_id' => $subscription->company_id,
            'subscription_id' => $subscription->id,
        ]);
    }
}
