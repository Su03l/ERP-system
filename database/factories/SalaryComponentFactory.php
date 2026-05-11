<?php

namespace Database\Factories;

use App\Enums\SalaryCalculationType;
use App\Enums\SalaryComponentStatus;
use App\Enums\SalaryComponentType;
use App\Models\Company;
use App\Models\SalaryComponent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalaryComponent>
 */
class SalaryComponentFactory extends Factory
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
            'name_ar' => fake()->words(2, true),
            'name_en' => fake()->words(2, true),
            'code' => fake()->unique()->bothify('COMP-###'),
            'type' => fake()->randomElement(SalaryComponentType::cases()),
            'calculation_type' => SalaryCalculationType::Fixed,
            'default_amount' => fake()->randomFloat(2, 100, 1500),
            'default_percentage' => null,
            'is_taxable' => false,
            'is_recurring' => true,
            'status' => SalaryComponentStatus::Active,
            'metadata' => [],
        ];
    }
}
