<?php

namespace Database\Factories;

use App\Enums\PlanStatus;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_ar' => 'خطة '.fake()->word(),
            'name_en' => fake()->words(2, true),
            'code' => 'PLAN-'.Str::upper(fake()->unique()->bothify('???-###')),
            'description_ar' => fake()->sentence(),
            'description_en' => fake()->sentence(),
            'price_monthly' => fake()->randomFloat(2, 0, 9999),
            'price_yearly' => fake()->randomFloat(2, 0, 99999),
            'currency' => 'SAR',
            'trial_days' => 14,
            'status' => PlanStatus::Active,
            'limits' => ['users' => 25],
            'features' => ['hr' => true],
            'metadata' => [],
        ];
    }
}
