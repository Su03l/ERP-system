<?php

namespace App\Models;

use App\Enums\AssetCategoryStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\AssetCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

#[Fillable([
    'company_id',
    'parent_id',
    'name_ar',
    'name_en',
    'code',
    'status',
    'description_ar',
    'description_en',
    'metadata',
])]
class AssetCategory extends Model
{
    /** @use HasFactory<AssetCategoryFactory> */
    use BelongsToCompany, HasFactory, SoftDeletes;

    /**
     * Get the parent asset category.
     *
     * @return BelongsTo<AssetCategory, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'parent_id');
    }

    /**
     * Get the child asset categories.
     *
     * @return HasMany<AssetCategory, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(AssetCategory::class, 'parent_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AssetCategoryStatus::class,
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (AssetCategory $category): void {
            if ($category->parent_id === null) {
                return;
            }

            if ($category->exists && (int) $category->parent_id === (int) $category->getKey()) {
                throw ValidationException::withMessages([
                    'parent_id' => __('assets.validation.asset_categories.parent_self'),
                ]);
            }

            $parent = AssetCategory::query()->find($category->parent_id);

            if ($parent === null || (int) $parent->company_id !== (int) $category->company_id) {
                throw ValidationException::withMessages([
                    'parent_id' => __('assets.validation.asset_categories.parent_company'),
                ]);
            }
        });
    }
}
