<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\ImportJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImportJob>
 */
class ImportJobFactory extends Factory
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
            'user_id' => User::factory(),
            'status' => 'pending',
            'file_path' => 'imports/'.fake()->uuid().'.csv',
            'entity_type' => fake()->randomElement(['employees', 'assets', 'contacts']),
            'module_key' => fake()->randomElement(['hr', 'assets', 'accounting']),
            'error_summary' => null,
            'processed_rows' => 0,
            'failed_rows' => 0,
            'total_rows' => 0,
            'started_at' => null,
            'finished_at' => null,
        ];
    }
}
