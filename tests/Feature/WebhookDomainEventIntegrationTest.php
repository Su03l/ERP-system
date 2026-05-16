<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Services\WebhookEventDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('creates deliveries only for enabled endpoints listening to selected events', function () {
    Queue::fake();
    $company = Company::factory()->create();
    $customer = Customer::factory()->for($company)->create();
    WebhookEndpoint::factory()->for($company)->create(['events' => ['customer.created'], 'status' => 'active']);
    WebhookEndpoint::factory()->for($company)->create(['events' => ['project.created'], 'status' => 'active']);
    WebhookEndpoint::factory()->for($company)->create(['events' => ['customer.created'], 'status' => 'inactive']);

    $deliveries = app(WebhookEventDispatcher::class)->dispatch('customer.created', $customer, $company->id);

    expect($deliveries)->toHaveCount(1)
        ->and(WebhookDelivery::query()->where('event_name', 'customer.created')->count())->toBe(1);
});
