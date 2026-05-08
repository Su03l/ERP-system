<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use App\Models\WorkflowAction;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkflowAction>
 */
class WorkflowActionFactory extends Factory
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
            'workflow_instance_id' => WorkflowInstance::factory(),
            'workflow_step_id' => WorkflowStep::factory(),
            'acted_by_id' => User::factory(),
            'action' => fake()->randomElement(['approved', 'rejected', 'returned']),
            'comment' => fake()->optional()->sentence(),
            'metadata' => [],
            'acted_at' => now(),
        ];
    }
}
