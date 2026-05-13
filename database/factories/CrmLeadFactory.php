<?php

namespace Database\Factories;

use App\Enums\LeadStatus;
use App\Models\Company;
use App\Models\CrmLead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CrmLead>
 */
class CrmLeadFactory extends Factory
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
            'assigned_user_id' => null,
            'name_ar' => fake()->name(),
            'name_en' => fake()->name(),
            'company_name' => fake()->optional()->company(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'source' => fake()->optional()->randomElement(['website', 'referral', 'campaign']),
            'status' => LeadStatus::New,
            'expected_value' => fake()->optional()->randomFloat(2, 1000, 100000),
            'notes_ar' => fake()->optional()->sentence(),
            'notes_en' => fake()->optional()->sentence(),
            'metadata' => [],
        ];
    }
}
