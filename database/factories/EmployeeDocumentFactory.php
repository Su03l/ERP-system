<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeDocument>
 */
class EmployeeDocumentFactory extends Factory
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
            'document_type' => fake()->randomElement(['national_id', 'passport', 'contract', 'certificate']),
            'title_ar' => 'مستند '.fake()->word(),
            'title_en' => fake()->optional()->words(2, true),
            'file_path' => fake()->optional()->filePath(),
            'issue_date' => fake()->optional()->date(),
            'expiry_date' => fake()->optional()->date(),
            'status' => 'active',
            'notes' => fake()->optional()->sentence(),
            'metadata' => [],
        ];
    }
}
