<?php

namespace Database\Factories;

use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkflowStep>
 */
class WorkflowStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workflow_id' => Workflow::factory(),
            'name' => fake()->words(2, true),
            'approver_type' => 'role',
            'approver_value' => (string) fake()->numberBetween(1, 100),
            'order' => fake()->unique()->numberBetween(1, 50),
            'conditions' => [],
        ];
    }
}
