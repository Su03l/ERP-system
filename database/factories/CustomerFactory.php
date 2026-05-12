<?php

namespace Database\Factories;

use App\Enums\CustomerStatus;
use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
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
            'code' => fake()->unique()->bothify('CUS-####'),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'tax_number' => fake()->optional()->numerify('###############'),
            'billing_address' => fake()->address(),
            'status' => CustomerStatus::Active,
            'metadata' => [],
        ];
    }
}
