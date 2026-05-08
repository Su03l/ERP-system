<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkflowInstance>
 */
class WorkflowInstanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $workflow = Workflow::factory();

        return [
            'company_id' => Company::factory(),
            'workflow_id' => $workflow,
            'current_step_id' => WorkflowStep::factory(),
            'requested_by_id' => User::factory(),
            'subject_type' => null,
            'subject_id' => null,
            'status' => 'pending',
            'payload' => [],
            'completed_at' => null,
        ];
    }
}
