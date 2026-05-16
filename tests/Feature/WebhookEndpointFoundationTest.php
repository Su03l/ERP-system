<?php

use App\Models\Company;
use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores tenant scoped webhook endpoints without exposing secret hashes', function () {
    $company = Company::factory()->create();
    $endpoint = WebhookEndpoint::factory()->for($company)->create([
        'events' => ['customer.created', 'invoice.paid'],
    ]);

    expect($endpoint->company->is($company))->toBeTrue()
        ->and($endpoint->listensFor('customer.created'))->toBeTrue()
        ->and($endpoint->toArray())->not->toHaveKey('secret_hash')
        ->and($company->webhookEndpoints()->first()->is($endpoint))->toBeTrue();
});
