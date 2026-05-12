<?php

namespace Database\Factories;

use App\Enums\PaymentDirection;
use App\Enums\PaymentStatus;
use App\Models\Company;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Payment> */
class PaymentFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'payable_type' => null,
            'payable_id' => null,
            'customer_id' => null,
            'vendor_id' => null,
            'payment_number' => fake()->unique()->bothify('PAY-####'),
            'payment_date' => fake()->date(),
            'direction' => PaymentDirection::Incoming,
            'method' => 'bank_transfer',
            'amount' => 100,
            'currency' => 'SAR',
            'reference' => fake()->optional()->bothify('REF-####'),
            'status' => PaymentStatus::Draft,
            'notes_ar' => null,
            'notes_en' => null,
            'posted_journal_entry_id' => null,
            'metadata' => [],
        ];
    }
}
