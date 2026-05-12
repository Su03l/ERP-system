<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesInvoiceLine>
 */
class SalesInvoiceLineFactory extends Factory
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
            'sales_invoice_id' => SalesInvoice::factory(),
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
