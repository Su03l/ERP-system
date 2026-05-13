<?php

namespace App\Models;

use App\Enums\LeadStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\CrmLeadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'assigned_user_id',
    'name_ar',
    'name_en',
    'company_name',
    'email',
    'phone',
    'source',
    'status',
    'expected_value',
    'notes_ar',
    'notes_en',
    'metadata',
])]
class CrmLead extends Model
{
    /** @use HasFactory<CrmLeadFactory> */
    use BelongsToCompany, HasFactory, SoftDeletes;

    /**
     * Get the user assigned to this lead.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Get contacts created from this lead.
     *
     * @return HasMany<CrmContact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(CrmContact::class, 'lead_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expected_value' => 'decimal:2',
            'status' => LeadStatus::class,
            'metadata' => 'array',
        ];
    }
}
