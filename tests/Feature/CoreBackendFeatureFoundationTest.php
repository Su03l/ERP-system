<?php

use App\Enums\CompanyModule;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\CompanyModuleService;
use App\Services\LocaleResolver;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

test('core backend foundation resolves tenant permissions audit locale and modules', function () {
    $company = Company::factory()->create(['locale' => 'fr']);
    $user = User::factory()->for($company)->create(['preferred_locale' => null]);
    $role = Role::factory()->for($company)->create();
    $permission = Permission::query()->firstOrCreate(
        ['key' => 'analytics.view'],
        ['name' => 'Analytics View'],
    );

    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $company->id]);

    $this->actingAs($user);

    $tenantContext = app(TenantContext::class);

    expect($tenantContext->company())->is($company)
        ->and($tenantContext->companyId())->toBe($company->id)
        ->and($user->company)->is($company)
        ->and($user->hasPermission('analytics.view'))->toBeTrue()
        ->and(Gate::forUser($user)->allows('analytics.view'))->toBeTrue()
        ->and(app(LocaleResolver::class)->resolveForRequest(request()))->toBe('ar');

    $updatedCompany = app(CompanyModuleService::class)->sync($company, [
        CompanyModule::Hr,
        'attendance',
        'unsupported',
    ]);

    expect($updatedCompany->hasModule('hr'))->toBeTrue()
        ->and($updatedCompany->hasModule('attendance'))->toBeTrue()
        ->and($updatedCompany->hasModule('payroll'))->toBeFalse()
        ->and($updatedCompany->settings['enabled_modules'])->toBe(['hr', 'attendance']);

    $auditLog = app(AuditLogger::class)->log(
        action: 'core.foundation.checked',
        auditable: $company,
        metadata: ['task' => 211],
    );

    expect($auditLog)->toBeInstanceOf(AuditLog::class)
        ->and($auditLog->company_id)->toBe($company->id)
        ->and($auditLog->user_id)->toBe($user->id)
        ->and($auditLog->metadata)->toBe(['task' => 211]);
});
