<?php

use App\Models\Company;
use App\Models\CrmLead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates the CRM leads schema', function () {
    expect(Schema::hasColumns('crm_leads', [
        'id',
        'company_id',
        'assigned_user_id',
        'name_ar',
        'name_en',
        'company_name',
        'email',
        'phone',
        'source',
        'status',
        'expected_value',
        'notes_ar',
        'notes_en',
        'metadata',
        'created_at',
        'updated_at',
        'deleted_at',
    ]))->toBeTrue();
});

it('stores tenant scoped CRM leads with assigned user relationship', function () {
    $company = Company::factory()->create();
    $assignedUser = User::factory()->for($company)->create();

    $lead = CrmLead::factory()->for($company)->create([
        'assigned_user_id' => $assignedUser->id,
        'name_ar' => 'عميل محتمل',
        'name_en' => 'Potential Lead',
        'expected_value' => '12500.50',
        'metadata' => ['channel' => 'website'],
    ]);

    expect($lead->company->is($company))->toBeTrue()
        ->and($company->crmLeads()->whereKey($lead)->exists())->toBeTrue()
        ->and($lead->assignedUser->is($assignedUser))->toBeTrue()
        ->and($assignedUser->assignedCrmLeads()->whereKey($lead)->exists())->toBeTrue()
        ->and($lead->expected_value)->toBe('12500.50')
        ->and($lead->metadata)->toBe(['channel' => 'website']);
});

it('scopes CRM leads to the current company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $lead = CrmLead::factory()->for($company)->create();
    CrmLead::factory()->for($otherCompany)->create();

    $this->actingAs($user);

    expect(CrmLead::query()->forCurrentCompany()->pluck('id')->all())->toBe([$lead->id]);
});

it('supports soft deleting CRM leads', function () {
    $lead = CrmLead::factory()->create();

    $lead->delete();

    expect(CrmLead::query()->whereKey($lead)->exists())->toBeFalse()
        ->and(CrmLead::withTrashed()->whereKey($lead)->exists())->toBeTrue();
});
