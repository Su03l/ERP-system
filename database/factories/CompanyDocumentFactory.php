<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\CompanyDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyDocument>
 */
class CompanyDocumentFactory extends Factory
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
            'document_type' => 'contract',
            'title_ar' => fake()->words(2, true),
            'title_en' => fake()->words(2, true),
            'file_path' => null,
            'issue_date' => fake()->optional()->date(),
            'expiry_date' => fake()->optional()->date(),
            'status' => 'active',
            'notes_ar' => null,
            'notes_en' => null,
            'metadata' => [],
        ];
    }
}
