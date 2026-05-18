<?php

use App\Models\Company;
use App\Models\CompanyApiToken;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Services\SecurityExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantTenantAuditPermission202(User $user, string $permissionKey): void
{
    $role = Role::factory()->for($user->company)->create();
    $permission = Permission::query()->firstOrCreate(['key' => $permissionKey], ['name' => $permissionKey]);
    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('keeps tenant owned models explicitly company fillable and scopeable', function () {
    $modelFiles = collect(glob(app_path('Models/*.php')));
    $violations = $modelFiles
        ->filter(fn (string $file): bool => str_contains(file_get_contents($file), 'use BelongsToCompany'))
        ->map(function (string $file): ?string {
            $class = 'App\\Models\\'.basename($file, '.php');
            $model = new $class;

            if (! in_array('company_id', $model->getFillable(), true) || ! method_exists($model, 'scopeForCompany')) {
                return $class;
            }

            return null;
        })
        ->filter()
        ->values()
        ->all();

    expect($violations)->toBe([]);
});

it('prevents security exports from leaking API token metadata across companies', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantTenantAuditPermission202($user, 'api_tokens.view');

    CompanyApiToken::factory()->for($company)->for($user)->create(['name' => 'Allowed token']);
    CompanyApiToken::factory()->for($otherCompany)->create(['name' => 'Other tenant token']);

    $rows = app(SecurityExportService::class)->apiTokens($user)['rows'];

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['name'])->toBe('Allowed token');
});

it('prevents webhook delivery routes from exposing another company delivery', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantTenantAuditPermission202($user, 'webhooks.view');

    $endpoint = WebhookEndpoint::factory()->for($otherCompany)->create();
    $delivery = WebhookDelivery::factory()->for($otherCompany)->for($endpoint, 'endpoint')->create();

    $this->actingAs($user)
        ->getJson(route('webhook-deliveries.show', $delivery))
        ->assertForbidden();
});
