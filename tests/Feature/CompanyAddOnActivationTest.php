<?php

use App\Actions\ActivateCompanyAddOn;
use App\Actions\DeactivateCompanyAddOn;
use App\Enums\CompanyAddOnStatus;
use App\Models\AddOn;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use App\Services\CheckCompanyAddOnAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('activates company add-ons and enables mapped modules without hardcoded add-on names', function () {
    Carbon::setTestNow('2026-05-14 00:00:00');
    $company = Company::factory()->create(['settings' => ['enabled_modules' => []]]);
    $addOn = AddOn::factory()->create(['feature_key' => 'analytics']);
    $actor = User::factory()->for($company)->create();

    $companyAddOn = app(ActivateCompanyAddOn::class)->handle($company, $addOn, ['metadata' => ['source' => 'test']], $actor);

    expect($companyAddOn->status)->toBe(CompanyAddOnStatus::Active)
        ->and($companyAddOn->company->is($company))->toBeTrue()
        ->and($companyAddOn->addOn->is($addOn))->toBeTrue()
        ->and($company->refresh()->settings['enabled_modules'])->toBe(['analytics'])
        ->and(app(CheckCompanyAddOnAccess::class)->handle($company, 'analytics'))->toBeTrue()
        ->and(AuditLog::query()->where('action', 'company_add_on.activated')->where('auditable_id', $companyAddOn->id)->exists())->toBeTrue();

    Carbon::setTestNow();
});

it('deactivates company add-ons and removes mapped modules when no active add-on still provides them', function () {
    Carbon::setTestNow('2026-05-14 00:00:00');
    $company = Company::factory()->create(['settings' => ['enabled_modules' => ['analytics']]]);
    $addOn = AddOn::factory()->create(['feature_key' => 'analytics']);
    $companyAddOn = app(ActivateCompanyAddOn::class)->handle($company, $addOn);

    $deactivated = app(DeactivateCompanyAddOn::class)->handle($companyAddOn, reason: 'not needed');

    expect($deactivated->status)->toBe(CompanyAddOnStatus::Inactive)
        ->and($deactivated->ends_at?->toDateString())->toBe('2026-05-14')
        ->and($company->refresh()->settings['enabled_modules'])->toBe([])
        ->and(app(CheckCompanyAddOnAccess::class)->handle($company, 'analytics'))->toBeFalse()
        ->and(AuditLog::query()->where('action', 'company_add_on.deactivated')->where('auditable_id', $companyAddOn->id)->exists())->toBeTrue();

    Carbon::setTestNow();
});

it('keeps mapped modules enabled while another active add-on provides the same feature', function () {
    $company = Company::factory()->create(['settings' => ['enabled_modules' => []]]);
    $firstAddOn = AddOn::factory()->create(['feature_key' => 'hr']);
    $secondAddOn = AddOn::factory()->create(['feature_key' => 'hr']);
    $firstCompanyAddOn = app(ActivateCompanyAddOn::class)->handle($company, $firstAddOn);
    app(ActivateCompanyAddOn::class)->handle($company, $secondAddOn);

    app(DeactivateCompanyAddOn::class)->handle($firstCompanyAddOn);

    expect($company->refresh()->settings['enabled_modules'])->toBe(['hr'])
        ->and(app(CheckCompanyAddOnAccess::class)->handle($company, 'hr'))->toBeTrue();
});

it('checks access by add-on record for unmapped feature keys', function () {
    $company = Company::factory()->create();
    $addOn = AddOn::factory()->create(['feature_key' => 'white_label']);

    expect(app(CheckCompanyAddOnAccess::class)->handle($company, $addOn))->toBeFalse();

    app(ActivateCompanyAddOn::class)->handle($company, $addOn);

    expect(app(CheckCompanyAddOnAccess::class)->handle($company, $addOn))->toBeTrue();
});
