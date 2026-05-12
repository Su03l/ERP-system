<?php

namespace App\Models;

use App\Enums\VendorStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\VendorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'name_ar',
    'name_en',
    'code',
    'email',
    'phone',
    'tax_number',
    'address',
    'status',
    'metadata',
])]
class Vendor extends Model
{
    /** @use HasFactory<VendorFactory> */
    use BelongsToCompany, HasFactory, SoftDeletes;

    /** @return HasMany<PurchaseInvoice, $this> */
    public function purchaseInvoices(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => VendorStatus::class,
            'metadata' => 'array',
        ];
    }
}
