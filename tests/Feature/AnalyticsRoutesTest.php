<?php

use App\Models\Company;
use App\Models\DashboardWidget;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function grantAnalyticsPermission(User $user, string $permissionKey): void
{
    $role = Role::factory()->for($user->company)->create();
    $permission = Permission::query()->firstOrCreate(['key' => $permissionKey], ['name' => $permissionKey]);
    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('exposes protected analytics KPI and report routes', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantAnalyticsPermission($user, 'analytics.view');
    grantAnalyticsPermission($user, 'reports.view');

    $this->actingAs($user)
        ->getJson(route('analytics.kpis.index'))
        ->assertSuccessful();

    $this->actingAs($user)
        ->getJson(route('analytics.reports.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.0.key', 'hr.employees');
});

it('creates chart data through a thin backend route', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantAnalyticsPermission($user, 'analytics.view');

    $this->actingAs($user)
        ->postJson(route('analytics.charts.store'), [
            'type' => 'bar',
            'labels' => ['A'],
            'datasets' => [['label' => 'Count', 'data' => [1]]],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.type', 'bar');
});

it('manages dashboard widgets with tenant scope', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantAnalyticsPermission($user, 'dashboard_widgets.view');
    grantAnalyticsPermission($user, 'dashboard_widgets.manage');
    DashboardWidget::factory()->for($company)->create(['widget_key' => 'hr.total']);

    $this->actingAs($user)
        ->getJson(route('dashboard-widgets.index'))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');

    $this->actingAs($user)
        ->postJson(route('dashboard-widgets.store'), [
            'widget_key' => 'finance.revenue',
            'module' => 'finance',
            'title_ar' => 'الإيرادات',
            'type' => 'kpi',
            'resolver' => 'finance.revenue',
        ])
        ->assertCreated()
        ->assertJsonPath('data.company_id', $company->id);
});

it('accepts report export requests and creates export jobs', function () {
    Queue::fake();
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantAnalyticsPermission($user, 'reports.export');
    grantAnalyticsPermission($user, 'employees.view');

    $this->actingAs($user)
        ->postJson(route('analytics.reports.export'), [
            'report_key' => 'hr.employees',
            'export_format' => 'csv',
            'queued' => true,
        ])
        ->assertAccepted()
        ->assertJsonPath('data.status', 'pending');
});
