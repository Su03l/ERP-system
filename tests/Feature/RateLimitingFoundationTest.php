<?php

use App\Models\Company;
use App\Models\CompanyApiToken;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('applies tenant scoped rate limiting to public API routes', function () {
    $company = Company::factory()->create();
    $plainToken = 'limited-token';
    CompanyApiToken::factory()->for($company)->create([
        'token' => hash('sha256', $plainToken),
        'abilities' => ['public-api.read'],
    ]);

    for ($i = 0; $i < 120; $i++) {
        $this->withToken($plainToken)->getJson(route('public-api.company.show'))->assertSuccessful();
    }

    $this->withToken($plainToken)->getJson(route('public-api.company.show'))->assertStatus(429);
});
