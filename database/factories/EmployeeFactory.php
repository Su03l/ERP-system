<?php

namespace Database\Factories;

use App\Enums\EmployeeStatus;
use App\Enums\Gender;
use App\Enums\WorkType;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
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
            'user_id' => null,
            'department_id' => null,
            'job_title_id' => null,
            'manager_id' => null,
            'employee_number' => fake()->unique()->bothify('EMP-####'),
            'first_name_ar' => 'اسم '.fake()->unique()->word(),
            'last_name_ar' => 'عائلة '.fake()->unique()->word(),
            'first_name_en' => fake()->firstName(),
            'last_name_en' => fake()->lastName(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'national_id' => fake()->optional()->numerify('##########'),
            'nationality' => fake()->optional()->country(),
            'gender' => fake()->optional()->randomElement(Gender::values()),
            'date_of_birth' => fake()->optional()->date(),
            'hire_date' => fake()->optional()->date(),
            'employment_status' => EmployeeStatus::Active->value,
            'work_type' => fake()->optional()->randomElement(WorkType::values()),
            'basic_salary' => fake()->optional()->randomFloat(2, 3000, 30000),
        ];
    }
}
