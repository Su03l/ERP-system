<?php

use App\Models\Company;
use App\Models\CompanyApiToken;
use App\Models\Customer;
use App\Models\Project;
use App\Models\SalesInvoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('exposes minimal tenant-scoped public API endpoints', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $plainToken = 'readonly-public-token';
    CompanyApiToken::factory()->for($company)->create([
        'token' => hash('sha256', $plainToken),
        'abilities' => ['public-api.read'],
    ]);
    $customer = Customer::factory()->for($company)->create(['name_ar' => 'عميل']);
    Customer::factory()->for($otherCompany)->create();
    SalesInvoice::factory()->for($company)->for($customer)->create(['invoice_number' => 'INV-001']);
    Project::factory()->for($company)->for($customer)->create(['code' => 'PRJ-001']);

    $this->withToken($plainToken)
        ->getJson(route('public-api.customers.index'))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name_ar', 'عميل');

    $this->withToken($plainToken)
        ->getJson(route('public-api.invoices.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.0.invoice_number', 'INV-001');

    $this->withToken($plainToken)
        ->getJson(route('public-api.projects.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.0.code', 'PRJ-001');
});
