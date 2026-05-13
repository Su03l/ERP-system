<?php

namespace Database\Factories;

use App\Enums\AssetCategoryStatus;
use App\Models\AssetCategory;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetCategory>
 */
class AssetCategoryFactory extends Factory
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
            'parent_id' => null,
            'name_ar' => fake()->words(2, true),
            'name_en' => fake()->words(2, true),
            'code' => fake()->unique()->bothify('AST-CAT-####'),
            'status' => AssetCategoryStatus::Active,
            'description_ar' => null,
            'description_en' => null,
            'metadata' => [],
        ];
    }
}
