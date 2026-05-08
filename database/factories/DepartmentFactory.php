<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
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
            'name_ar' => 'قسم '.fake()->unique()->word(),
            'name_en' => fake()->unique()->words(2, true),
            'code' => fake()->unique()->bothify('DEP-###'),
            'parent_id' => null,
            'manager_id' => null,
            'status' => 'active',
            'description' => fake()->optional()->sentence(),
        ];
    }
}
