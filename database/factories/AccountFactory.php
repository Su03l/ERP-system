<?php

namespace Database\Factories;

use App\Enums\AccountNormalBalance;
use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(AccountType::cases());

        return [
            'company_id' => Company::factory(),
            'parent_id' => null,
            'code' => fake()->unique()->numerify('####'),
            'name_ar' => fake()->words(2, true),
            'name_en' => fake()->words(2, true),
            'type' => $type,
            'normal_balance' => in_array($type, [AccountType::Asset, AccountType::Expense], true)
                ? AccountNormalBalance::Debit
                : AccountNormalBalance::Credit,
            'level' => 1,
            'is_active' => true,
            'is_system' => false,
            'metadata' => [],
        ];
    }
}
