<?php

use App\Models\Company;
use App\Models\CompanyApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores tenant API token metadata without exposing token hashes', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    $token = CompanyApiToken::factory()->for($company)->for($user)->create([
        'abilities' => ['reports.export'],
        'token' => hash('sha256', 'plain-token'),
    ]);

    expect($token->company->is($company))->toBeTrue()
        ->and($token->user->is($user))->toBeTrue()
        ->and($token->can('reports.export'))->toBeTrue()
        ->and($token->toArray())->not->toHaveKey('token')
        ->and($company->apiTokens()->first()->is($token))->toBeTrue()
        ->and($user->companyApiTokens()->first()->is($token))->toBeTrue();
});
