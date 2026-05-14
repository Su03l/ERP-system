<?php

use App\Enums\SubscriptionStatus;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Employee;
use App\Models\ExportJob;
use App\Models\Plan;
use App\Models\UsageSnapshot;
use App\Models\User;
use App\Services\UsageTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates tenant scoped usage snapshot schema', function () {
    expect(Schema::hasColumns('usage_snapshots', [
        'id',
        'company_id',
        'users_count',
        'employees_count',
        'storage_usage_mb',
        'active_modules_count',
        'api_requests_count',
        'exports_count',
        'metadata',
        'captured_at',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

it('captures current usage counts for a company', function () {
    $company = Company::factory()->create([
        'settings' => [
            'enabled_modules' => ['hr', 'payroll', 'invalid'],
            'usage' => [
                'storage_mb' => 512,
                'api_requests_count' => 25,
            ],
        ],
    ]);
    User::factory()->for($company)->count(2)->create();
    Employee::factory()->for($company)->count(3)->create();
    ExportJob::factory()->for($company)->count(4)->create();

    $snapshot = app(UsageTrackingService::class)->capture($company);

    expect($snapshot->company->is($company))->toBeTrue()
        ->and($snapshot->users_count)->toBe(2)
        ->and($snapshot->employees_count)->toBe(3)
        ->and($snapshot->storage_usage_mb)->toBe(512)
        ->and($snapshot->active_modules_count)->toBe(2)
        ->and($snapshot->api_requests_count)->toBe(25)
        ->and($snapshot->exports_count)->toBe(4)
        ->and($snapshot->metadata)->toBe(['source' => 'usage_tracking_service']);
});

it('uses latest snapshots for plan limit checks', function () {
    $company = Company::factory()->create();
    $plan = Plan::factory()->create([
        'limits' => [
            'users' => 2,
            'employees' => 5,
            'storage_mb' => 1000,
        ],
    ]);
    CompanySubscription::factory()->for($company)->for($plan)->create(['status' => SubscriptionStatus::Active]);
    UsageSnapshot::factory()->for($company)->create([
        'users_count' => 2,
        'employees_count' => 3,
        'storage_usage_mb' => 900,
        'captured_at' => now(),
    ]);

    $service = app(UsageTrackingService::class);

    expect($service->checkUsersLimit($company)->allowed)->toBeFalse()
        ->and($service->checkEmployeesLimit($company)->allowed)->toBeTrue()
        ->and($service->checkStorageLimit($company, additionalStorageMb: 150)->allowed)->toBeFalse();
});

it('scopes usage snapshots to the current company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $snapshot = UsageSnapshot::factory()->for($company)->create();
    UsageSnapshot::factory()->for($otherCompany)->create();

    $this->actingAs($user);

    expect(UsageSnapshot::query()->forCurrentCompany()->pluck('id')->all())->toBe([$snapshot->id]);
});
