<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveType>
 */
class LeaveTypeFactory extends Factory
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
            'name_ar' => 'إجازة '.fake()->unique()->word(),
            'name_en' => fake()->unique()->words(2, true),
            'code' => fake()->unique()->bothify('LV-###'),
            'default_days_per_year' => fake()->numberBetween(5, 30),
            'is_paid' => true,
            'requires_approval' => true,
            'allow_negative_balance' => false,
            'status' => 'active',
            'description' => fake()->optional()->sentence(),
        ];
    }
}
