<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\SecuritySetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SecuritySetting>
 */
class SecuritySettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'session_timeout_minutes' => 120,
            'password_policy' => [
                'min_length' => 10,
                'require_numbers' => true,
                'require_symbols' => false,
            ],
            'two_factor_authentication_enabled' => false,
            'allowed_login_ips' => null,
            'audit_retention_days' => 365,
            'export_approval_required' => false,
            'metadata' => null,
        ];
    }
}
