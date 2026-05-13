<?php

namespace Database\Factories;

use App\Enums\ProjectPriority;
use App\Enums\ProjectTaskStatus;
use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectTask>
 */
class ProjectTaskFactory extends Factory
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
            'project_id' => Project::factory(),
            'assigned_employee_id' => null,
            'parent_task_id' => null,
            'task_code' => fake()->unique()->bothify('TSK-####'),
            'title_ar' => 'مهمة '.fake()->unique()->word(),
            'title_en' => fake()->words(3, true),
            'description_ar' => fake()->optional()->sentence(),
            'description_en' => fake()->optional()->sentence(),
            'start_date' => fake()->optional()->date(),
            'due_date' => fake()->optional()->date(),
            'completed_at' => null,
            'status' => ProjectTaskStatus::Todo,
            'priority' => ProjectPriority::Medium,
            'estimated_hours' => fake()->optional()->randomFloat(2, 1, 120),
            'actual_hours' => null,
            'progress_percentage' => 0,
            'workflow_instance_id' => null,
            'metadata' => [],
        ];
    }
}
