<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\LeaveTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'name_ar',
    'name_en',
    'code',
    'default_days_per_year',
    'is_paid',
    'requires_approval',
    'allow_negative_balance',
    'status',
    'description',
])]
class LeaveType extends Model
{
    /** @use HasFactory<LeaveTypeFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get balances for this leave type.
     *
     * @return HasMany<LeaveBalance, $this>
     */
    public function balances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_days_per_year' => 'decimal:2',
            'is_paid' => 'boolean',
            'requires_approval' => 'boolean',
            'allow_negative_balance' => 'boolean',
        ];
    }
}
