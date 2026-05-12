<?php

namespace Database\Factories;

use App\Enums\VendorStatus;
use App\Models\Company;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
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
            'name_ar' => fake()->company(),
            'name_en' => fake()->company(),
            'code' => fake()->unique()->bothify('VEN-####'),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'tax_number' => fake()->optional()->numerify('###############'),
            'address' => fake()->address(),
            'status' => VendorStatus::Active,
            'metadata' => [],
        ];
    }
}
