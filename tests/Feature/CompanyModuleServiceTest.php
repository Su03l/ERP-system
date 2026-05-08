<?php

use App\Enums\CompanyModule;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use App\Services\CompanyModuleService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('company modules can be synced and checked', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    $this->actingAs($user);

    $updatedCompany = app(CompanyModuleService::class)->sync($company, [
        CompanyModule::Hr,
        'payroll',
        'unknown-module',
        'hr',
    ]);

    expect($updatedCompany->settings['enabled_modules'])->toBe(['hr', 'payroll'])
        ->and(app(CompanyModuleService::class)->isEnabled($updatedCompany, CompanyModule::Hr))->toBeTrue()
        ->and($updatedCompany->hasModule('payroll'))->toBeTrue()
        ->and($updatedCompany->hasModule('accounting'))->toBeFalse();

    $auditLog = AuditLog::where('action', 'company.modules.updated')->first();

    expect($auditLog)->not->toBeNull()
        ->and($auditLog->company_id)->toBe($company->id)
        ->and($auditLog->user_id)->toBe($user->id);
});

test('company modules cannot be synced outside current tenant', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    $this->actingAs($user);

    app(CompanyModuleService::class)->sync($otherCompany, [
        CompanyModule::Accounting,
    ]);
})->throws(AuthorizationException::class);

test('company module checks ignore unsupported stored module keys', function () {
    $company = Company::factory()->create([
        'settings' => [
            'enabled_modules' => ['hr', 'not-real'],
        ],
    ]);

    expect(app(CompanyModuleService::class)->enabledModules($company))->toBe(['hr']);
});
