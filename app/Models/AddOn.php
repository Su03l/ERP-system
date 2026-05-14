<?php

namespace App\Models;

use App\Enums\AddOnStatus;
use Database\Factories\AddOnFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name_ar',
    'name_en',
    'code',
    'description_ar',
    'description_en',
    'category',
    'price_monthly',
    'price_yearly',
    'status',
    'feature_key',
    'metadata',
])]
class AddOn extends Model
{
    /** @use HasFactory<AddOnFactory> */
    use HasFactory;

    /**
     * Get company activations for this add-on.
     *
     * @return HasMany<CompanyAddOn, $this>
     */
    public function companyAddOns(): HasMany
    {
        return $this->hasMany(CompanyAddOn::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'status' => AddOnStatus::class,
            'metadata' => 'array',
        ];
    }
}
