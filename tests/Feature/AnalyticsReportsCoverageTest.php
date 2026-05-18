<?php

use App\DTOs\KpiDateRange;
use App\DTOs\ReportFilter;
use App\Jobs\ProcessReportExportJob;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\AnalyticsCacheService;
use App\Services\KpiRegistry;
use App\Services\ReportRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function grantAnalyticsCoveragePermissions218(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::query()->firstOrCreate(
            ['key' => $permissionKey],
            ['name' => $permissionKey, 'description' => null],
        );

        $role->permissions()->syncWithoutDetaching([$permission->id]);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

test('kpi registry endpoint returns allowed kpis only and protects sensitive payroll kpis', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantAnalyticsCoveragePermissions218($user, ['analytics.view', 'employees.view']);

    $response = $this->actingAs($user)
        ->getJson('/analytics/kpis')
        ->assertSuccessful();

    $keys = collect($response->json('data'))->pluck('key');

    expect($keys)->toContain('hr.total_employees')
        ->and($keys)->not->toContain('payroll.total_cost');

    grantAnalyticsCoveragePermissions218($user, ['payroll_runs.view']);

    $keys = collect($this->actingAs($user)->getJson('/analytics/kpis')->json('data'))->pluck('key');

    expect($keys)->toContain('payroll.total_cost');
});

test('report filters validate and report execution returns resolver structure', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantAnalyticsCoveragePermissions218($user, ['reports.run']);

    $this->actingAs($user)
        ->postJson('/analytics/reports/execute', [
            'report_key' => 'hr.employees',
            'company_id' => $otherCompany->id,
            'export_format' => 'csv',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['company_id']);

    $this->actingAs($user)
        ->postJson('/analytics/reports/execute', [
            'report_key' => 'hr.employees',
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
            'locale' => 'ar',
            'export_format' => 'csv',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.report.key', 'hr.employees')
        ->assertJsonPath('data.filters.locale', 'ar')
        ->assertJsonStructure(['data' => ['report', 'filters', 'resolver_class']]);
});

test('report export creates queued export job for authorized report access', function () {
    Queue::fake();

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    Employee::factory()->for($company)->create(['employee_number' => 'EMP-218']);
    grantAnalyticsCoveragePermissions218($user, ['reports.export', 'employees.view']);

    $this->actingAs($user)
        ->postJson('/analytics/reports/export', [
            'report_key' => 'hr.employees',
            'export_format' => 'csv',
            'queued' => true,
        ])
        ->assertAccepted()
        ->assertJsonPath('data.company_id', $company->id)
        ->assertJsonPath('data.entity_type', 'hr.employees');

    Queue::assertPushed(ProcessReportExportJob::class);
});

test('analytics cache keys include tenant scope and normalized filters', function () {
    $cache = app(AnalyticsCacheService::class);
    $range = KpiDateRange::fromDates('2026-05-01', '2026-05-31');
    $firstKey = $cache->key('kpi', 'hr.total_employees', 10, [
        'filters' => ['department_id' => 1],
        'date_range' => $range->toArray(),
    ]);
    $sameKey = $cache->key('kpi', 'hr.total_employees', 10, [
        'date_range' => $range->toArray(),
        'filters' => ['department_id' => 1],
    ]);
    $otherCompanyKey = $cache->key('kpi', 'hr.total_employees', 11, [
        'filters' => ['department_id' => 1],
        'date_range' => $range->toArray(),
    ]);
    $platformKey = $cache->key('report_summary', 'saas.revenue', null, ReportFilter::fromArray([])->toArray());

    expect($firstKey)->toBe($sameKey)
        ->and($firstKey)->toContain('company:10')
        ->and($otherCompanyKey)->toContain('company:11')
        ->and($platformKey)->toContain('platform')
        ->and($firstKey)->not->toBe($otherCompanyKey);
});

test('saas kpi permissions remain platform-only', function () {
    $company = Company::factory()->create();
    $tenantUser = User::factory()->for($company)->create();
    $platformUser = User::factory()->create(['company_id' => null]);

    grantAnalyticsCoveragePermissions218($tenantUser, ['analytics.view', 'subscription_invoices.view']);

    $definition = KpiRegistry::default()->definition('saas.mrr');
    $report = ReportRegistry::default()->definition('saas.revenue');

    expect($definition->requiredPermission)->toBe('subscription_invoices.view')
        ->and($report->requiredPermission)->toBe('subscription_invoices.view')
        ->and(Gate::forUser($tenantUser)->denies('subscription_invoices.view'))->toBeTrue()
        ->and(Gate::forUser($platformUser)->allows('subscription_invoices.view'))->toBeTrue();
});
