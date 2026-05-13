<?php

use App\Enums\ContactStatus;
use App\Enums\LeadStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\CrmContact;
use App\Models\CrmLead;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantCrmControllerPermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('lists CRM leads with tenant scope and filters', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantCrmControllerPermissions($actor, ['crm_leads.view']);
    $assignedUser = User::factory()->for($company)->create();
    $matchingLead = CrmLead::factory()->for($company)->create([
        'assigned_user_id' => $assignedUser->id,
        'source' => 'website',
        'status' => LeadStatus::Qualified,
        'name_ar' => 'عميل مطابق',
    ]);
    CrmLead::factory()->for($company)->create(['source' => 'referral']);
    CrmLead::factory()->for(Company::factory())->create(['source' => 'website']);

    $this->actingAs($actor)
        ->getJson(route('crm-leads.index', [
            'status' => LeadStatus::Qualified->value,
            'assigned_user_id' => $assignedUser->id,
            'source' => 'website',
            'search' => 'مطابق',
        ]))
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $matchingLead->id)
        ->assertJsonCount(1, 'data');
});

it('creates updates shows and archives CRM leads through thin endpoints', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantCrmControllerPermissions($actor, ['crm_leads.view', 'crm_leads.create', 'crm_leads.update', 'crm_leads.delete']);

    $leadId = $this->actingAs($actor)
        ->postJson(route('crm-leads.store'), [
            'name_ar' => 'عميل جديد',
            'status' => LeadStatus::New->value,
            'expected_value' => '1250.00',
        ])
        ->assertSuccessful()
        ->json('data.id');

    $this->actingAs($actor)
        ->patchJson(route('crm-leads.update', $leadId), [
            'status' => LeadStatus::Contacted->value,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.status', LeadStatus::Contacted->value);

    $this->actingAs($actor)
        ->getJson(route('crm-leads.show', $leadId))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $leadId);

    $this->actingAs($actor)
        ->deleteJson(route('crm-leads.destroy', $leadId))
        ->assertNoContent();

    expect(CrmLead::withTrashed()->find($leadId)->trashed())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'crm_lead.archived')->where('auditable_id', $leadId)->exists())->toBeTrue();
});

it('lists CRM contacts with tenant scope and filters', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantCrmControllerPermissions($actor, ['crm_contacts.view']);
    $customer = Customer::factory()->for($company)->create();
    $lead = CrmLead::factory()->for($company)->create();
    $matchingContact = CrmContact::factory()->for($company)->create([
        'customer_id' => $customer->id,
        'lead_id' => $lead->id,
        'status' => ContactStatus::Active,
        'name_ar' => 'جهة مطابقة',
    ]);
    CrmContact::factory()->for($company)->create(['status' => ContactStatus::Inactive]);
    CrmContact::factory()->for(Company::factory())->create(['status' => ContactStatus::Active]);

    $this->actingAs($actor)
        ->getJson(route('crm-contacts.index', [
            'status' => ContactStatus::Active->value,
            'customer_id' => $customer->id,
            'lead_id' => $lead->id,
            'search' => 'مطابقة',
        ]))
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $matchingContact->id)
        ->assertJsonCount(1, 'data');
});

it('creates updates shows and archives CRM contacts through thin endpoints', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $customer = Customer::factory()->for($company)->create();
    grantCrmControllerPermissions($actor, ['crm_contacts.view', 'crm_contacts.create', 'crm_contacts.update', 'crm_contacts.delete']);

    $contactId = $this->actingAs($actor)
        ->postJson(route('crm-contacts.store'), [
            'customer_id' => $customer->id,
            'name_ar' => 'جهة جديدة',
            'status' => ContactStatus::Active->value,
        ])
        ->assertSuccessful()
        ->json('data.id');

    $this->actingAs($actor)
        ->patchJson(route('crm-contacts.update', $contactId), [
            'status' => ContactStatus::Inactive->value,
            'position' => 'Manager',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.status', ContactStatus::Inactive->value);

    $this->actingAs($actor)
        ->getJson(route('crm-contacts.show', $contactId))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $contactId);

    $this->actingAs($actor)
        ->deleteJson(route('crm-contacts.destroy', $contactId))
        ->assertNoContent();

    expect(CrmContact::query()->whereKey($contactId)->exists())->toBeFalse()
        ->and(AuditLog::query()->where('action', 'crm_contact.archived')->where('auditable_id', $contactId)->exists())->toBeTrue();
});

it('prevents CRM endpoints from exposing another company records', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantCrmControllerPermissions($actor, ['crm_leads.view', 'crm_contacts.view']);
    $otherLead = CrmLead::factory()->for(Company::factory())->create();
    $otherContact = CrmContact::factory()->for(Company::factory())->create();

    $this->actingAs($actor)->getJson(route('crm-leads.show', $otherLead))->assertForbidden();
    $this->actingAs($actor)->getJson(route('crm-contacts.show', $otherContact))->assertForbidden();
});
