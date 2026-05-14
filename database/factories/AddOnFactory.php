<?php

namespace Database\Factories;

use App\Enums\AddOnStatus;
use App\Models\AddOn;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AddOn>
 */
class AddOnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_ar' => 'إضافة '.fake()->word(),
            'name_en' => fake()->words(2, true),
            'code' => 'ADDON-'.Str::upper(fake()->unique()->bothify('???-###')),
            'description_ar' => fake()->sentence(),
            'description_en' => fake()->sentence(),
            'category' => fake()->randomElement(['hr', 'accounting', 'analytics']),
            'price_monthly' => fake()->randomFloat(2, 0, 999),
            'price_yearly' => fake()->randomFloat(2, 0, 9999),
            'status' => AddOnStatus::Active,
            'feature_key' => null,
            'metadata' => [],
        ];
    }
}
