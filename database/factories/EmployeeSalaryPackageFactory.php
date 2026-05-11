<?php

namespace Database\Factories;

use App\Enums\SalaryPackageStatus;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeSalaryPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeSalaryPackage>
 */
class EmployeeSalaryPackageFactory extends Factory
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
            'employee_id' => fn (array $attributes): int => Employee::factory()->create([
                'company_id' => $attributes['company_id'],
            ])->id,
            'basic_salary' => fake()->randomFloat(2, 3000, 20000),
            'housing_allowance' => fake()->randomFloat(2, 0, 5000),
            'transportation_allowance' => fake()->randomFloat(2, 0, 2000),
            'effective_from' => now()->startOfMonth(),
            'effective_to' => null,
            'status' => SalaryPackageStatus::Active,
            'metadata' => [],
        ];
    }
}
