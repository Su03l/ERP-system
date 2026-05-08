<?php

use App\Models\Company;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tenant context resolves the authenticated users company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    $this->actingAs($user);

    $tenantContext = app(TenantContext::class);

    expect($tenantContext->company())->is($company)
        ->and($tenantContext->companyId())->toBe($company->id);
});

test('tenant context returns null when no company is assigned', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $tenantContext = app(TenantContext::class);

    expect($tenantContext->company())->toBeNull()
        ->and($tenantContext->companyId())->toBeNull();
});

test('tenant context returns null when there is no authenticated user', function () {
    $tenantContext = app(TenantContext::class);

    expect($tenantContext->company())->toBeNull()
        ->and($tenantContext->companyId())->toBeNull();
});
