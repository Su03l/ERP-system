<?php

namespace Database\Factories;

use App\Enums\JobTitleStatus;
use App\Models\Company;
use App\Models\JobTitle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobTitle>
 */
class JobTitleFactory extends Factory
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
            'name_ar' => 'مسمى '.fake()->unique()->word(),
            'name_en' => fake()->unique()->jobTitle(),
            'code' => fake()->unique()->bothify('JOB-###'),
            'description' => fake()->optional()->sentence(),
            'status' => JobTitleStatus::Active->value,
        ];
    }
}
