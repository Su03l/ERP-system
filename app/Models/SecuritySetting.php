<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\SecuritySettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'session_timeout_minutes',
    'password_policy',
    'two_factor_authentication_enabled',
    'allowed_login_ips',
    'audit_retention_days',
    'export_approval_required',
    'metadata',
])]
class SecuritySetting extends Model
{
    /** @use HasFactory<SecuritySettingFactory> */
    use BelongsToCompany, HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'password_policy' => 'array',
            'two_factor_authentication_enabled' => 'boolean',
            'allowed_login_ips' => 'array',
            'audit_retention_days' => 'integer',
            'export_approval_required' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
