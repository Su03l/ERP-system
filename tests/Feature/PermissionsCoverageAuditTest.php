<?php

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\SecurityExportService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

function grantPermissionAudit203(User $user, string $permissionKey): void
{
    $role = Role::factory()->for($user->company)->create();
    $permission = Permission::query()->firstOrCreate(['key' => $permissionKey], ['name' => $permissionKey]);
    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('keeps sensitive authenticated route groups behind auth middleware', function (string $routeName) {
    $middleware = Route::getRoutes()->getByName($routeName)?->gatherMiddleware() ?? [];

    expect(collect($middleware)->contains(fn (string $middleware): bool => str_starts_with($middleware, 'auth')))->toBeTrue();
})->with([
    'payroll-runs.index',
    'payroll-run-items.index',
    'journal-entries.index',
    'subscription-invoices.index',
    'security-settings.index',
    'audit-logs.index',
    'api-tokens.index',
    'analytics.reports.export',
]);

it('requires explicit salary permission before salary package access', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    $this->actingAs($user)
        ->getJson(route('salary-packages.index'))
        ->assertForbidden();

    grantPermissionAudit203($user, 'salary_packages.view');

    $this->getJson(route('salary-packages.index'))
        ->assertSuccessful();
});

it('requires explicit security export permission before audit export service access', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    expect(fn () => app(SecurityExportService::class)->auditLogs($user))
        ->toThrow(AuthorizationException::class);
});
