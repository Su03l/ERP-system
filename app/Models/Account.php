<?php

namespace App\Models;

use App\Enums\AccountNormalBalance;
use App\Enums\AccountType;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'parent_id',
    'code',
    'name_ar',
    'name_en',
    'type',
    'normal_balance',
    'level',
    'is_active',
    'is_system',
    'metadata',
])]
class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the parent account.
     *
     * @return BelongsTo<Account, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    /**
     * Get the child accounts.
     *
     * @return HasMany<Account, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'normal_balance' => AccountNormalBalance::class,
            'level' => 'integer',
            'is_active' => 'boolean',
            'is_system' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
