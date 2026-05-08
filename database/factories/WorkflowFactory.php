<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Workflow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workflow>
 */
class WorkflowFactory extends Factory
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
            'name' => fake()->words(3, true),
            'module_key' => fake()->randomElement(['leave', 'payroll', 'accounting', 'assets', 'documents']),
            'trigger_type' => fake()->randomElement(['created', 'submitted', 'posted']),
            'status' => 'active',
            'conditions' => [],
        ];
    }
}
