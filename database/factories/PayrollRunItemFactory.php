<?php

namespace Database\Factories;

use App\Enums\PayrollRunItemStatus;
use App\Models\Company;
use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\PayrollRunItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollRunItem>
 */
class PayrollRunItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $basicSalary = fake()->randomFloat(2, 3000, 20000);
        $totalAllowances = fake()->randomFloat(2, 0, 5000);
        $totalDeductions = fake()->randomFloat(2, 0, 2500);
        $overtimeAmount = fake()->randomFloat(2, 0, 1000);
        $grossSalary = $basicSalary + $totalAllowances + $overtimeAmount;

        return [
            'company_id' => Company::factory(),
            'payroll_run_id' => fn (array $attributes): int => PayrollRun::factory()->create([
                'company_id' => $attributes['company_id'],
            ])->id,
            'employee_id' => fn (array $attributes): int => Employee::factory()->create([
                'company_id' => $attributes['company_id'],
            ])->id,
            'basic_salary' => $basicSalary,
            'gross_salary' => $grossSalary,
            'total_allowances' => $totalAllowances,
            'total_deductions' => $totalDeductions,
            'net_salary' => $grossSalary - $totalDeductions,
            'attendance_deduction' => null,
            'leave_deduction' => null,
            'overtime_amount' => $overtimeAmount,
            'status' => PayrollRunItemStatus::Draft,
            'metadata' => [],
        ];
    }
}
