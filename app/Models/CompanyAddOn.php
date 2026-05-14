<?php

namespace App\Models;

use App\Enums\CompanyAddOnStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\CompanyAddOnFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'add_on_id',
    'status',
    'starts_at',
    'ends_at',
    'metadata',
])]
class CompanyAddOn extends Model
{
    /** @use HasFactory<CompanyAddOnFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the platform add-on attached to this company.
     *
     * @return BelongsTo<AddOn, $this>
     */
    public function addOn(): BelongsTo
    {
        return $this->belongsTo(AddOn::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CompanyAddOnStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
