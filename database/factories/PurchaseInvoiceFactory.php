<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Company;
use App\Models\PurchaseInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PurchaseInvoice> */
class PurchaseInvoiceFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'vendor_id' => null,
            'invoice_number' => fake()->unique()->bothify('PINV-####'),
            'vendor_invoice_number' => fake()->optional()->bothify('V-####'),
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
}
