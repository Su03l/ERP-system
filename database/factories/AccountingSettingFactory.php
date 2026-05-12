<?php

namespace Database\Factories;

use App\Models\AccountingSetting;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccountingSetting>
 */
class AccountingSettingFactory extends Factory
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
            'fiscal_year_start_month' => 1,
            'default_currency' => 'SAR',
            'tax_enabled' => false,
            'default_vat_rate' => 0,
            'invoice_numbering_prefix' => 'INV',
            'journal_numbering_prefix' => 'JRN',
            'accounting_approval_required' => true,
            'metadata' => [],
        ];
    }
}
