<?php

namespace Database\Factories;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Company;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
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
            'project_manager_id' => null,
            'code' => fake()->unique()->bothify('PRJ-####'),
            'name_ar' => 'مشروع '.fake()->unique()->word(),
            'name_en' => fake()->words(3, true),
            'description_ar' => fake()->optional()->sentence(),
            'description_en' => fake()->optional()->sentence(),
            'start_date' => fake()->optional()->date(),
            'end_date' => fake()->optional()->date(),
            'budget' => fake()->optional()->randomFloat(2, 10000, 500000),
            'status' => ProjectStatus::Draft,
            'priority' => ProjectPriority::Medium,
            'progress_percentage' => 0,
            'metadata' => [],
        ];
    }
}
