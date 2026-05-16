<?php

use App\Models\Company;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('tracks webhook deliveries with retry-ready status fields', function () {
    $company = Company::factory()->create();
    $endpoint = WebhookEndpoint::factory()->for($company)->create();
    $delivery = WebhookDelivery::factory()->for($company)->for($endpoint, 'endpoint')->create([
        'event_name' => 'invoice.paid',
        'payload' => ['invoice_id' => 10],
        'attempt_count' => 1,
        'status' => 'failed',
        'next_retry_at' => now()->addMinutes(5),
    ]);

    expect($delivery->company->is($company))->toBeTrue()
        ->and($delivery->endpoint->is($endpoint))->toBeTrue()
        ->and($delivery->payload['invoice_id'])->toBe(10)
        ->and($company->webhookDeliveries()->first()->is($delivery))->toBeTrue();
});
