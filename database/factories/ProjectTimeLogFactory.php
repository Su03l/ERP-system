<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectTimeLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectTimeLog>
 */
class ProjectTimeLogFactory extends Factory
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
            'project_task_id' => null,
            'employee_id' => Employee::factory(),
            'log_date' => fake()->date(),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'total_minutes' => 60,
            'is_billable' => false,
            'notes_ar' => fake()->optional()->sentence(),
            'notes_en' => fake()->optional()->sentence(),
            'metadata' => [],
        ];
    }
}
