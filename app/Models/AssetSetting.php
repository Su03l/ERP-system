<?php

namespace App\Models;

use App\Enums\AssetDepreciationMethod;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\AssetSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'asset_code_prefix',
    'depreciation_enabled',
    'default_depreciation_method',
    'custody_approval_required',
    'asset_return_approval_required',
    'metadata',
])]
class AssetSetting extends Model
{
    /** @use HasFactory<AssetSettingFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'depreciation_enabled' => 'boolean',
            'default_depreciation_method' => AssetDepreciationMethod::class,
            'custody_approval_required' => 'boolean',
            'asset_return_approval_required' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
