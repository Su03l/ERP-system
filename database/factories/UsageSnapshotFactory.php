<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\UsageSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UsageSnapshot>
 */
class UsageSnapshotFactory extends Factory
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
            'users_count' => fake()->numberBetween(0, 100),
            'employees_count' => fake()->numberBetween(0, 500),
            'storage_usage_mb' => fake()->numberBetween(0, 10000),
            'active_modules_count' => fake()->numberBetween(0, 10),
            'api_requests_count' => fake()->numberBetween(0, 100000),
            'exports_count' => fake()->numberBetween(0, 1000),
            'metadata' => [],
            'captured_at' => now(),
        ];
    }
}
