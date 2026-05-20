<?php

use App\Models\Company;
use App\Models\DashboardWidget;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantUserPermission(User $user, string $permissionKey): void
{
    $role = Role::factory()->for($user->company)->create();
    $permission = Permission::query()->firstOrCreate(['key' => $permissionKey], ['name' => $permissionKey]);
    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('redirects unauthenticated guests to login page', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

it('renders the dashboard and auto-seeds default widgets for an authenticated user', function () {
    $company = Company::factory()->create(['locale' => 'ar']);
    $user = User::factory()->for($company)->create(['preferred_locale' => 'ar']);

    // Grant required permissions so the widgets aren't filtered out
    grantUserPermission($user, 'employees.view');
    grantUserPermission($user, 'attendance.view');
    grantUserPermission($user, 'financial_reports.view');
    grantUserPermission($user, 'leave_requests.view');

    // Verify initially no widgets exist for this company
    expect(DashboardWidget::where('company_id', $company->id)->count())->toBe(0);

    $response = $this->actingAs($user)
        ->get('/dashboard');

    $response->assertSuccessful();

    // Verify widgets were automatically seeded
    expect(DashboardWidget::where('company_id', $company->id)->count())->toBeGreaterThan(0);

    // Verify Arabic titles and widget layout elements are rendered
    $response->assertSee('إجمالي الموظفين', false)
        ->assertSee('معدل الحضور اليومي', false)
        ->assertSee('صافي الأرباح', false)
        ->assertSee('توزيع الموظفين على الأقسام', false);
});

it('filters dashboard data by date range', function () {
    $company = Company::factory()->create(['locale' => 'ar']);
    $user = User::factory()->for($company)->create(['preferred_locale' => 'ar']);

    $dateFrom = '2026-05-01';
    $dateTo = '2026-05-31';

    $response = $this->actingAs($user)
        ->get("/dashboard?date_from={$dateFrom}&date_to={$dateTo}");

    $response->assertSuccessful()
        ->assertSee($dateFrom, false)
        ->assertSee($dateTo, false);
});

it('hides widgets that the user does not have permission to view', function () {
    $company = Company::factory()->create(['locale' => 'ar']);
    $user = User::factory()->for($company)->create(['preferred_locale' => 'ar']);

    // Seed a specific widget requiring high permission
    $widget = DashboardWidget::factory()->create([
        'company_id' => $company->id,
        'widget_key' => 'payroll.by_department',
        'title_ar' => 'مسيرة رواتب سرية للغاية',
        'title_en' => 'Top Secret Payroll',
        'required_permission' => 'payroll_runs.view',
    ]);

    // When the user has no permissions, the widget should be filtered out
    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertSuccessful();
    $response->assertDontSee('مسيرة رواتب سرية للغاية', false);
});
