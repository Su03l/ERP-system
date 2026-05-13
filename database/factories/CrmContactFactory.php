<?php

namespace Database\Factories;

use App\Enums\ContactStatus;
use App\Models\Company;
use App\Models\CrmContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CrmContact>
 */
class CrmContactFactory extends Factory
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
            'customer_id' => null,
            'lead_id' => null,
            'name_ar' => fake()->name(),
            'name_en' => fake()->name(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'position' => fake()->optional()->jobTitle(),
            'notes_ar' => fake()->optional()->sentence(),
            'notes_en' => fake()->optional()->sentence(),
            'status' => ContactStatus::Active,
            'metadata' => [],
        ];
    }
}
