<?php

use App\Models\Company;
use App\Models\CompanyApiToken;
use App\Models\WebhookEndpoint;
use App\Services\WebhookDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('keeps webhook delivery payload sanitization recursive', function () {
    $endpoint = WebhookEndpoint::factory()->create();

    $delivery = app(WebhookDeliveryService::class)->createDelivery($endpoint, 'customer.created', [
        'customer' => [
            'id' => 10,
            'token' => 'nested-secret',
            'profile' => [
                'api_key' => 'nested-api-key',
                'name' => 'Customer',
            ],
        ],
        'access_token' => 'top-level-token',
    ]);

    expect($delivery->payload)->toBe([
        'customer' => [
            'id' => 10,
            'profile' => [
                'name' => 'Customer',
            ],
        ],
    ]);
});

it('keeps public API authentication scoped to the token company during review', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $plainToken = 'review-token';

    CompanyApiToken::factory()->for($company)->create([
        'token' => hash('sha256', $plainToken),
        'abilities' => ['public-api.read'],
    ]);

    $this->withToken($plainToken)
        ->getJson(route('public-api.company.show'))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $company->id)
        ->assertJsonMissing(['id' => $otherCompany->id]);
});
