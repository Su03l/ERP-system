<?php

use App\Enums\CompanyAddOnStatus;
use App\Enums\CompanyModule;
use App\Enums\SubscriptionStatus;
use App\Models\AddOn;
use App\Models\Company;
use App\Models\CompanyAddOn;
use App\Models\CompanySubscription;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\Role;
use App\Models\User;
use App\Services\CheckCompanyAddOnAccess;
use App\Services\PlanLimitsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('keeps platform abilities out of tenant role permission shortcuts', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $role = Role::factory()->for($company)->create();
    $permission = Permission::factory()->create(['key' => 'plans.view']);

    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $company->id]);

    expect($user->hasPermission('plans.view', $company->id))->toBeFalse();
});

it('keeps plan modules and marketplace add-ons aligned for access checks', function () {
    $company = Company::factory()->create([
        'settings' => ['enabled_modules' => [CompanyModule::Hr->value]],
    ]);
    $plan = Plan::factory()->create([
        'features' => ['enabled_modules' => [CompanyModule::Hr->value]],
    ]);
    CompanySubscription::factory()->for($company)->for($plan)->create([
        'status' => SubscriptionStatus::Active,
        'ends_at' => now()->addMonth(),
    ]);
    $addOn = AddOn::factory()->create(['feature_key' => 'advanced_reports']);
    CompanyAddOn::factory()->for($company)->for($addOn)->create([
        'status' => CompanyAddOnStatus::Active,
    ]);

    expect(app(PlanLimitsService::class)->moduleEnabled($company, CompanyModule::Hr)->allowed)->toBeTrue()
        ->and(app(CheckCompanyAddOnAccess::class)->handle($company, 'advanced_reports'))->toBeTrue();
});
