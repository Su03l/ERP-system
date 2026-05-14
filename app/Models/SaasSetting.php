<?php

namespace App\Models;

use Database\Factories\SaasSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'default_trial_days',
    'default_currency',
    'billing_enabled',
    'marketplace_enabled',
    'invoice_numbering_prefix',
    'subscription_grace_period_days',
    'metadata',
])]
class SaasSetting extends Model
{
    /** @use HasFactory<SaasSettingFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_trial_days' => 'integer',
            'billing_enabled' => 'boolean',
            'marketplace_enabled' => 'boolean',
            'subscription_grace_period_days' => 'integer',
            'metadata' => 'array',
        ];
    }
}
