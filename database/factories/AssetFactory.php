<?php

namespace Database\Factories;

use App\Enums\AssetDepreciationMethod;
use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
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
            'asset_category_id' => null,
            'asset_code' => fake()->unique()->bothify('AST-####'),
            'name_ar' => fake()->words(2, true),
            'name_en' => fake()->words(2, true),
            'serial_number' => fake()->optional()->bothify('SN-####-????'),
            'purchase_date' => fake()->optional()->date(),
            'purchase_cost' => fake()->optional()->randomFloat(2, 100, 10000),
            'current_value' => fake()->optional()->randomFloat(2, 50, 9000),
            'status' => AssetStatus::Available,
            'location' => fake()->optional()->city(),
            'assigned_employee_id' => null,
            'depreciation_method' => AssetDepreciationMethod::StraightLine,
            'useful_life_months' => 60,
            'salvage_value' => 0,
            'metadata' => [],
        ];
    }
}
