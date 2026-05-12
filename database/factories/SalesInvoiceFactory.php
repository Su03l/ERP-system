<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Company;
use App\Models\Customer;
use App\Models\SalesInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesInvoice>
 */
class SalesInvoiceFactory extends Factory
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
            'customer_id' => null,
            'invoice_number' => fake()->unique()->bothify('INV-####'),
            'invoice_date' => fake()->date(),
            'due_date' => fake()->optional()->date(),
            'status' => InvoiceStatus::Draft,
            'subtotal' => 100,
            'tax_amount' => 15,
            'discount_amount' => 0,
            'total_amount' => 115,
            'paid_amount' => 0,
            'balance_due' => 115,
            'currency' => 'SAR',
            'notes_ar' => null,
            'notes_en' => null,
            'posted_journal_entry_id' => null,
            'metadata' => [],
        ];
    }

    public function withCustomer(?Customer $customer = null): static
    {
        return $this->state(function (array $attributes) use ($customer): array {
            $customer ??= Customer::factory()->create(['company_id' => $attributes['company_id']]);

            return [
                'customer_id' => $customer->id,
            ];
        });
    }
}
