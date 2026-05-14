<?php

use App\Enums\PlanStatus;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates system level SaaS plans schema', function () {
    expect(Schema::hasColumns('plans', [
        'id',
        'name_ar',
        'name_en',
        'code',
        'description_ar',
        'description_en',
        'price_monthly',
        'price_yearly',
        'currency',
        'trial_days',
        'status',
        'limits',
        'features',
        'metadata',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

it('stores plans as system records with decimal prices and json configuration', function () {
    $plan = Plan::factory()->create([
        'name_ar' => 'الخطة الأساسية',
        'name_en' => 'Basic Plan',
        'code' => 'BASIC',
        'price_monthly' => '99.50',
        'price_yearly' => '999.00',
        'currency' => 'SAR',
        'trial_days' => 30,
        'status' => PlanStatus::Active,
        'limits' => ['users' => 10, 'storage_gb' => 5],
        'features' => ['hr' => true, 'payroll' => false],
        'metadata' => ['audience' => 'starter'],
    ]);

    expect($plan->name_ar)->toBe('الخطة الأساسية')
        ->and($plan->price_monthly)->toBe('99.50')
        ->and($plan->price_yearly)->toBe('999.00')
        ->and($plan->status)->toBe(PlanStatus::Active)
        ->and($plan->limits)->toBe(['users' => 10, 'storage_gb' => 5])
        ->and($plan->features)->toBe(['hr' => true, 'payroll' => false])
        ->and($plan->metadata)->toBe(['audience' => 'starter'])
        ->and($plan->getAttributes())->not->toHaveKey('company_id');
});

it('provides localized SaaS plan status labels', function () {
    app()->setLocale('ar');

    expect(PlanStatus::Active->label())->toBe('نشط')
        ->and(PlanStatus::Inactive->label())->toBe('غير نشط')
        ->and(PlanStatus::values())->toBe(['active', 'inactive', 'archived']);
});
