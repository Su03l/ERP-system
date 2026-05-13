<?php

use App\Models\Company;
use App\Models\CrmContact;
use App\Models\CrmLead;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates the CRM contacts schema', function () {
    expect(Schema::hasColumns('crm_contacts', [
        'id',
        'company_id',
        'customer_id',
        'lead_id',
        'name_ar',
        'name_en',
        'email',
        'phone',
        'position',
        'notes_ar',
        'notes_en',
        'status',
        'metadata',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

it('stores tenant scoped CRM contacts with customer and lead relationships', function () {
    $company = Company::factory()->create();
    $customer = Customer::factory()->for($company)->create();
    $lead = CrmLead::factory()->for($company)->create();

    $contact = CrmContact::factory()->for($company)->create([
        'customer_id' => $customer->id,
        'lead_id' => $lead->id,
        'name_ar' => 'جهة اتصال',
        'name_en' => 'Contact Person',
        'metadata' => ['preferred_channel' => 'email'],
    ]);

    expect($contact->company->is($company))->toBeTrue()
        ->and($company->crmContacts()->whereKey($contact)->exists())->toBeTrue()
        ->and($contact->customer->is($customer))->toBeTrue()
        ->and($contact->lead->is($lead))->toBeTrue()
        ->and($customer->crmContacts()->whereKey($contact)->exists())->toBeTrue()
        ->and($lead->contacts()->whereKey($contact)->exists())->toBeTrue()
        ->and($contact->metadata)->toBe(['preferred_channel' => 'email']);
});

it('scopes CRM contacts to the current company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $contact = CrmContact::factory()->for($company)->create();
    CrmContact::factory()->for($otherCompany)->create();

    $this->actingAs($user);

    expect(CrmContact::query()->forCurrentCompany()->pluck('id')->all())->toBe([$contact->id]);
});
