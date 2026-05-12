<?php

namespace Database\Factories;

use App\Enums\JournalEntrySource;
use App\Enums\JournalEntryStatus;
use App\Models\Company;
use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JournalEntry>
 */
class JournalEntryFactory extends Factory
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
            'journal_number' => fake()->unique()->bothify('JRN-####'),
            'entry_date' => fake()->date(),
            'description_ar' => fake()->sentence(),
            'description_en' => fake()->sentence(),
            'source' => JournalEntrySource::Manual,
            'source_type' => null,
            'source_id' => null,
            'status' => JournalEntryStatus::Draft,
            'posted_by' => null,
            'posted_at' => null,
            'approved_by' => null,
            'approved_at' => null,
            'workflow_instance_id' => null,
            'metadata' => [],
        ];
    }
}
