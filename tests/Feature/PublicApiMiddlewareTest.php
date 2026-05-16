<?php

use App\Models\Company;
use App\Models\CompanyApiToken;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('authenticates company API tokens and resolves tenant context', function () {
    $company = Company::factory()->create();
    $plainToken = 'public-token-value';
    CompanyApiToken::factory()->for($company)->create([
        'user_id' => null,
        'token' => hash('sha256', $plainToken),
        'abilities' => ['public-api.read'],
    ]);

    $this->withToken($plainToken)
        ->getJson(route('public-api.company.show'))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $company->id);

    expect(CompanyApiToken::query()->first()->last_used_at)->not->toBeNull();
});

it('rejects revoked expired or under-scoped public API tokens safely', function () {
    $company = Company::factory()->create();

    CompanyApiToken::factory()->for($company)->create([
        'token' => hash('sha256', 'revoked-token'),
        'abilities' => ['public-api.read'],
        'revoked_at' => now(),
    ]);
    CompanyApiToken::factory()->for($company)->create([
        'token' => hash('sha256', 'expired-token'),
        'abilities' => ['public-api.read'],
        'expires_at' => now()->subMinute(),
    ]);
    CompanyApiToken::factory()->for($company)->create([
        'token' => hash('sha256', 'wrong-ability-token'),
        'abilities' => ['customers.read'],
    ]);

    $this->withToken('revoked-token')->getJson(route('public-api.company.show'))->assertUnauthorized();
    $this->withToken('expired-token')->getJson(route('public-api.company.show'))->assertUnauthorized();
    $this->withToken('wrong-ability-token')->getJson(route('public-api.company.show'))->assertForbidden();
});

it('lets tenant context read company from public API request attributes', function () {
    $company = Company::factory()->create();
    request()->attributes->set('company', $company);
    request()->attributes->set('company_id', $company->id);

    expect(app(TenantContext::class)->company()?->is($company))->toBeTrue()
        ->and(app(TenantContext::class)->companyId())->toBe($company->id);
});
