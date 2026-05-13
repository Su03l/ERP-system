<?php

namespace App\Models;

use App\Enums\AssetDepreciationMethod;
use App\Enums\AssetStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\AssetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

#[Fillable([
    'company_id',
    'asset_category_id',
    'asset_code',
    'name_ar',
    'name_en',
    'serial_number',
    'purchase_date',
    'purchase_cost',
    'current_value',
    'status',
    'location',
    'assigned_employee_id',
    'depreciation_method',
    'useful_life_months',
    'salvage_value',
    'metadata',
])]
class Asset extends Model
{
    /** @use HasFactory<AssetFactory> */
    use BelongsToCompany, HasFactory, SoftDeletes;

    /**
     * Get the asset category this asset belongs to.
     *
     * @return BelongsTo<AssetCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    /**
     * Get the employee this asset is assigned to.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'purchase_cost' => 'decimal:2',
            'current_value' => 'decimal:2',
            'status' => AssetStatus::class,
            'depreciation_method' => AssetDepreciationMethod::class,
            'useful_life_months' => 'integer',
            'salvage_value' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Asset $asset): void {
            if ($asset->asset_category_id !== null) {
                $category = AssetCategory::query()->find($asset->asset_category_id);

                if ($category === null || (int) $category->company_id !== (int) $asset->company_id) {
                    throw ValidationException::withMessages([
                        'asset_category_id' => __('assets.validation.assets.category_company'),
                    ]);
                }
            }

            if ($asset->assigned_employee_id !== null) {
                $employee = Employee::query()->find($asset->assigned_employee_id);

                if ($employee === null || (int) $employee->company_id !== (int) $asset->company_id) {
                    throw ValidationException::withMessages([
                        'assigned_employee_id' => __('assets.validation.assets.assigned_employee_company'),
                    ]);
                }
            }
        });
    }
}
