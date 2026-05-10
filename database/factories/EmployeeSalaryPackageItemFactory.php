<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\EmployeeSalaryPackage;
use App\Models\EmployeeSalaryPackageItem;
use App\Models\SalaryComponent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeSalaryPackageItem>
 */
class EmployeeSalaryPackageItemFactory extends Factory
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
            'employee_salary_package_id' => fn (array $attributes): int => EmployeeSalaryPackage::factory()->create([
                'company_id' => $attributes['company_id'],
            ])->id,
            'salary_component_id' => fn (array $attributes): int => SalaryComponent::factory()->create([
                'company_id' => $attributes['company_id'],
            ])->id,
            'amount' => fake()->randomFloat(2, 100, 1500),
            'percentage' => null,
        ];
    }
}
