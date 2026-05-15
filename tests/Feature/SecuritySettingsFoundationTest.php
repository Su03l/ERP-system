<?php

use App\Models\Company;
use App\Models\SecuritySetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores tenant scoped security settings with placeholder policies', function () {
    $company = Company::factory()->create();

    $settings = SecuritySetting::factory()->for($company)->create([
        'session_timeout_minutes' => 90,
        'password_policy' => ['min_length' => 12],
        'two_factor_authentication_enabled' => true,
        'allowed_login_ips' => ['10.0.0.1'],
        'audit_retention_days' => 730,
        'export_approval_required' => true,
    ]);

    expect($settings->company->is($company))->toBeTrue()
        ->and($settings->password_policy['min_length'])->toBe(12)
        ->and($settings->allowed_login_ips)->toBe(['10.0.0.1'])
        ->and($settings->two_factor_authentication_enabled)->toBeTrue()
        ->and($company->securitySetting->is($settings))->toBeTrue();
});
