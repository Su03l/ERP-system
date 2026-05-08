<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\ImportJob;
use App\Models\MigrationSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MigrationSession>
 */
class MigrationSessionFactory extends Factory
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
            'import_job_id' => null,
            'uploaded_file_path' => 'migration-uploads/'.fake()->uuid().'.csv',
            'target_entity' => fake()->randomElement(['employees', 'assets', 'contacts']),
            'module_key' => fake()->randomElement(['hr', 'assets', 'accounting']),
            'column_mapping' => [],
            'validation_result' => null,
            'dry_run_status' => 'pending',
            'final_import_status' => 'pending',
        ];
    }

    public function withImportJob(): static
    {
        return $this->state(fn (array $attributes) => [
            'import_job_id' => ImportJob::factory(),
        ]);
    }
}
