<?php

namespace Database\Factories;

use App\Enums\CompanyAddOnStatus;
use App\Models\AddOn;
use App\Models\Company;
use App\Models\CompanyAddOn;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyAddOn>
 */
class CompanyAddOnFactory extends Factory
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
            'add_on_id' => AddOn::factory(),
            'status' => CompanyAddOnStatus::Active,
            'starts_at' => now(),
            'ends_at' => null,
            'metadata' => [],
        ];
    }
}
