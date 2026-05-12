<?php

namespace Database\Factories;

use App\Enums\AccountNormalBalance;
use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JournalEntryLine>
 */
class JournalEntryLineFactory extends Factory
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
            'journal_entry_id' => fn (array $attributes): int => JournalEntry::factory()->create([
                'company_id' => $attributes['company_id'],
            ])->id,
            'account_id' => fn (array $attributes): int => Account::factory()->create([
                'company_id' => $attributes['company_id'],
                'type' => AccountType::Asset,
                'normal_balance' => AccountNormalBalance::Debit,
            ])->id,
            'description_ar' => fake()->sentence(),
            'description_en' => fake()->sentence(),
            'debit' => 0,
            'credit' => 0,
            'line_order' => fake()->numberBetween(1, 20),
            'metadata' => [],
        ];
    }
}
