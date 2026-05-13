<?php

namespace Database\Factories;

use App\Enums\AssetDepreciationMethod;
use App\Models\AssetSetting;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetSetting>
 */
class AssetSettingFactory extends Factory
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
            'asset_code_prefix' => 'AST',
            'depreciation_enabled' => true,
            'default_depreciation_method' => AssetDepreciationMethod::StraightLine,
            'custody_approval_required' => true,
            'asset_return_approval_required' => true,
            'metadata' => [],
        ];
    }
}
