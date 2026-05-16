<?php

use App\Models\Company;
use App\Models\Customer;
use App\Services\WebhookEventRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers explicit safe webhook events and payload resolvers', function () {
    $registry = app(WebhookEventRegistry::class);
    $events = collect($registry->definitions())->map->toArray()->keyBy('name');

    expect($events->keys())->toContain('customer.created', 'sales_invoice.issued', 'payment.received', 'project.created', 'employee.created', 'document.expiring', 'payroll.approved')
        ->and($events['customer.created']['module'])->toBe('crm')
        ->and($events['customer.created']['required_module'])->toBe('crm');
});

it('builds safe event payloads without secrets', function () {
    $customer = Customer::factory()->for(Company::factory())->create(['name_ar' => 'عميل']);

    $payload = app(WebhookEventRegistry::class)->payload('customer.created', $customer);

    expect($payload['id'])->toBe($customer->id)
        ->and($payload)->toHaveKey('email')
        ->and($payload)->not->toHaveKey('metadata');
});
