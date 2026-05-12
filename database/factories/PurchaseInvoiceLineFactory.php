<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PurchaseInvoiceLine> */
class PurchaseInvoiceLineFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'purchase_invoice_id' => PurchaseInvoice::factory(),
            'description_ar' => fake()->words(3, true),
            'description_en' => fake()->words(3, true),
            'quantity' => 1,
            'unit_price' => 100,
            'discount_amount' => 0,
            'tax_rate' => 15,
            'tax_amount' => 15,
            'line_total' => 115,
            'metadata' => [],
        ];
    }
}
