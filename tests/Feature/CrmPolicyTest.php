<?php

use App\Models\Company;
use App\Models\CrmContact;
use App\Models\CrmLead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

function grantCrmPolicyPermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('protects CRM lead permissions and company boundaries', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantCrmPolicyPermissions($actor, [
        'crm_leads.view',
        'crm_leads.create',
        'crm_leads.update',
        'crm_leads.delete',
        'crm_leads.convert',
    ]);
    $lead = CrmLead::factory()->for($company)->create();
    $otherLead = CrmLead::factory()->for(Company::factory())->create();

    expect(Gate::forUser($actor)->allows('viewAny', CrmLead::class))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('create', CrmLead::class))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('view', $lead))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('update', $lead))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('delete', $lead))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('convert', $lead))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('view', $otherLead))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('convert', $otherLead))->toBeTrue();
});

it('protects CRM contact permissions and company boundaries', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantCrmPolicyPermissions($actor, [
        'crm_contacts.view',
        'crm_contacts.create',
        'crm_contacts.update',
        'crm_contacts.delete',
    ]);
    $contact = CrmContact::factory()->for($company)->create();
    $otherContact = CrmContact::factory()->for(Company::factory())->create();

    expect(Gate::forUser($actor)->allows('viewAny', CrmContact::class))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('create', CrmContact::class))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('view', $contact))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('update', $contact))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('delete', $contact))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('view', $otherContact))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('delete', $otherContact))->toBeTrue();
});

it('denies CRM access without permission grants', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $lead = CrmLead::factory()->for($company)->create();
    $contact = CrmContact::factory()->for($company)->create();

    expect(Gate::forUser($actor)->denies('viewAny', CrmLead::class))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('create', CrmLead::class))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('update', $lead))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('viewAny', CrmContact::class))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('create', CrmContact::class))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('update', $contact))->toBeTrue();
});
