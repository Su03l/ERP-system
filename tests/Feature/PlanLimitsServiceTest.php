<?php

use App\Enums\CompanyModule;
use App\Enums\SubscriptionStatus;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Employee;
use App\Models\Plan;
use App\Models\User;
use App\Services\PlanLimitsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('checks user and employee limits from the active subscription plan', function () {
    $company = Company::factory()->create();
    $plan = Plan::factory()->create([
        'limits' => [
            'users' => 2,
            'employees' => 1,
        ],
    ]);
    CompanySubscription::factory()->for($company)->for($plan)->create(['status' => SubscriptionStatus::Active]);
    User::factory()->for($company)->count(2)->create();
    Employee::factory()->for($company)->create();

    $service = app(PlanLimitsService::class);

    $users = $service->checkUsersLimit($company);
    $employees = $service->checkEmployeesLimit($company);

    expect($users->allowed)->toBeFalse()
        ->and($users->limit)->toBe(2)
        ->and($users->current)->toBe(2)
        ->and($users->metadata['projected'])->toBe(3)
        ->and($employees->allowed)->toBeFalse()
        ->and($employees->message)->toBe(__('saas.limits.denied', ['limit' => __('saas.limit_keys.employees')]));
});

it('allows unlimited limits when no plan limit is configured', function () {
    $company = Company::factory()->create();
    $plan = Plan::factory()->create(['limits' => []]);
    CompanySubscription::factory()->for($company)->for($plan)->create(['status' => SubscriptionStatus::Trialing]);

    $result = app(PlanLimitsService::class)->checkStorageLimit($company, currentStorageMb: 500, additionalStorageMb: 250);

    expect($result->allowed)->toBeTrue()
        ->and($result->limit)->toBeNull()
        ->and($result->current)->toBe(500);
});

it('checks enabled modules and feature access from plan features', function () {
    $company = Company::factory()->create();
    $plan = Plan::factory()->create([
        'features' => [
            'enabled_modules' => ['hr', 'payroll', 'bad-module'],
            'api_access' => true,
            'advanced_reports' => false,
            'marketplace' => true,
        ],
    ]);
    CompanySubscription::factory()->for($company)->for($plan)->create(['status' => SubscriptionStatus::Grace]);

    $service = app(PlanLimitsService::class);

    expect($service->moduleEnabled($company, CompanyModule::Hr)->allowed)->toBeTrue()
        ->and($service->moduleEnabled($company, CompanyModule::Accounting)->allowed)->toBeFalse()
        ->and($service->apiAccess($company)->allowed)->toBeTrue()
        ->and($service->advancedReportsAccess($company)->allowed)->toBeFalse()
        ->and($service->marketplaceAccess($company)->allowed)->toBeTrue();
});

it('denies features safely when a company has no active subscription', function () {
    $company = Company::factory()->create();

    $result = app(PlanLimitsService::class)->apiAccess($company);

    expect($result->allowed)->toBeFalse()
        ->and($result->key)->toBe('api_access');
});
