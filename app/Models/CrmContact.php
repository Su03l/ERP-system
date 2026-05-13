<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\CrmContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'customer_id',
    'lead_id',
    'name_ar',
    'name_en',
    'email',
    'phone',
    'position',
    'notes_ar',
    'notes_en',
    'status',
    'metadata',
])]
class CrmContact extends Model
{
    /** @use HasFactory<CrmContactFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the customer attached to this contact.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the lead attached to this contact.
     *
     * @return BelongsTo<CrmLead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(CrmLead::class, 'lead_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
