<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->jobTitle();

        return [
            'company_id' => Company::factory(),
            'name' => $name,
            'key' => Str::slug($name),
            'description' => fake()->sentence(),
        ];
    }
}
