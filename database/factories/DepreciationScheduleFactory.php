<?php

namespace Database\Factories;

use App\Enums\DepreciationScheduleStatus;
use App\Models\Asset;
use App\Models\Company;
use App\Models\DepreciationSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DepreciationSchedule>
 */
class DepreciationScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $company = Company::factory();

        return [
            'company_id' => $company,
            'asset_id' => Asset::factory()->for($company),
            'period_date' => fake()->date(),
            'depreciation_amount' => 100,
            'accumulated_depreciation' => 100,
            'book_value' => 900,
            'status' => DepreciationScheduleStatus::Calculated,
            'posted_journal_entry_id' => null,
            'metadata' => [],
        ];
    }
}
