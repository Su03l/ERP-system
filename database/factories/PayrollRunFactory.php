<?php

namespace Database\Factories;

use App\Enums\PayrollRunStatus;
use App\Models\Company;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollRun>
 */
class PayrollRunFactory extends Factory
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
            'payroll_period_id' => fn (array $attributes): int => PayrollPeriod::factory()->create([
                'company_id' => $attributes['company_id'],
            ])->id,
            'run_number' => fake()->unique()->bothify('RUN-####'),
            'status' => PayrollRunStatus::Draft,
            'total_employees' => 0,
            'gross_amount' => 0,
            'total_allowances' => 0,
            'total_deductions' => 0,
            'net_amount' => 0,
            'generated_by' => null,
            'generated_at' => null,
            'approved_by' => null,
            'approved_at' => null,
            'workflow_instance_id' => null,
            'metadata' => [],
        ];
    }
}
