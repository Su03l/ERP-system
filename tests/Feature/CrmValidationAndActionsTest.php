<?php

use App\Actions\ConvertLeadToCustomer;
use App\Actions\CreateCrmContact;
use App\Actions\CreateCrmLead;
use App\Actions\UpdateCrmContact;
use App\Actions\UpdateCrmLead;
use App\Enums\ContactStatus;
use App\Enums\LeadStatus;
use App\Http\Requests\StoreCrmContactRequest;
use App\Http\Requests\StoreCrmLeadRequest;
use App\Http\Requests\UpdateCrmContactRequest;
use App\Http\Requests\UpdateCrmLeadRequest;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\CrmContact;
use App\Models\CrmLead;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Route::post('/test/crm-leads', fn (StoreCrmLeadRequest $request) => $request->validated());
    Route::patch('/test/crm-leads/{crm_lead}', fn (UpdateCrmLeadRequest $request, CrmLead $crmLead) => $request->validated());
    Route::post('/test/crm-contacts', fn (StoreCrmContactRequest $request) => $request->validated());
    Route::patch('/test/crm-contacts/{crm_contact}', fn (UpdateCrmContactRequest $request, CrmContact $crmContact) => $request->validated());
});

function grantCrmPermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('validates CRM lead payloads with tenant scoped assigned users', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $otherUser = User::factory()->for(Company::factory())->create();

    $this->actingAs($actor)
        ->postJson('/test/crm-leads', [
            'assigned_user_id' => $otherUser->id,
            'status' => 'bad',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['assigned_user_id', 'name_ar', 'status']);

    $this->actingAs($actor)
        ->postJson('/test/crm-leads', [
            'name_ar' => 'عميل محتمل',
            'status' => LeadStatus::New->value,
            'expected_value' => '1000.25',
        ])
        ->assertSuccessful();
});

it('validates CRM contacts against current company customer and lead IDs', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $otherCompany = Company::factory()->create();
    $otherCustomer = Customer::factory()->for($otherCompany)->create();
    $otherLead = CrmLead::factory()->for($otherCompany)->create();

    $this->actingAs($actor)
        ->postJson('/test/crm-contacts', [
            'customer_id' => $otherCustomer->id,
            'lead_id' => $otherLead->id,
            'status' => 'bad',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['customer_id', 'lead_id', 'name_ar', 'status']);

    $this->actingAs($actor)
        ->postJson('/test/crm-contacts', [
            'name_ar' => 'جهة اتصال',
            'status' => ContactStatus::Active->value,
        ])
        ->assertSuccessful();
});

it('creates and updates CRM leads with tenant ownership and audit logging', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $assignedUser = User::factory()->for($company)->create();
    grantCrmPermissions($actor, ['crm_leads.create', 'crm_leads.update']);
    $this->actingAs($actor);

    $lead = app(CreateCrmLead::class)->handle([
        'company_id' => Company::factory()->create()->id,
        'assigned_user_id' => $assignedUser->id,
        'name_ar' => 'عميل محتمل',
        'status' => LeadStatus::New,
        'expected_value' => '5000.00',
    ]);

    app(UpdateCrmLead::class)->handle($lead, [
        'name_ar' => 'عميل محدث',
        'status' => LeadStatus::Qualified,
    ]);

    expect($lead->refresh()->company_id)->toBe($company->id)
        ->and($lead->assigned_user_id)->toBe($assignedUser->id)
        ->and($lead->status)->toBe(LeadStatus::Qualified)
        ->and(AuditLog::query()->where('action', 'crm_lead.created')->where('auditable_id', $lead->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'crm_lead.updated')->where('auditable_id', $lead->id)->exists())->toBeTrue();
});

it('rejects cross-company lead assignment in actions', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $otherUser = User::factory()->for(Company::factory())->create();
    grantCrmPermissions($actor, ['crm_leads.create']);
    $this->actingAs($actor);

    app(CreateCrmLead::class)->handle([
        'assigned_user_id' => $otherUser->id,
        'name_ar' => 'عميل محتمل',
        'status' => LeadStatus::New,
    ]);
})->throws(ValidationException::class);

it('creates and updates CRM contacts with tenant ownership and audit logging', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $customer = Customer::factory()->for($company)->create();
    $lead = CrmLead::factory()->for($company)->create();
    grantCrmPermissions($actor, ['crm_contacts.create', 'crm_contacts.update']);
    $this->actingAs($actor);

    $contact = app(CreateCrmContact::class)->handle([
        'company_id' => Company::factory()->create()->id,
        'customer_id' => $customer->id,
        'lead_id' => $lead->id,
        'name_ar' => 'جهة اتصال',
        'status' => ContactStatus::Active,
    ]);

    app(UpdateCrmContact::class)->handle($contact, [
        'position' => 'Sales Manager',
        'status' => ContactStatus::Inactive,
    ]);

    expect($contact->refresh()->company_id)->toBe($company->id)
        ->and($contact->customer_id)->toBe($customer->id)
        ->and($contact->lead_id)->toBe($lead->id)
        ->and($contact->status)->toBe(ContactStatus::Inactive)
        ->and(AuditLog::query()->where('action', 'crm_contact.created')->where('auditable_id', $contact->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'crm_contact.updated')->where('auditable_id', $contact->id)->exists())->toBeTrue();
});

it('rejects cross-company contact relationships in actions', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $otherCustomer = Customer::factory()->for(Company::factory())->create();
    grantCrmPermissions($actor, ['crm_contacts.create']);
    $this->actingAs($actor);

    app(CreateCrmContact::class)->handle([
        'customer_id' => $otherCustomer->id,
        'name_ar' => 'جهة اتصال',
        'status' => ContactStatus::Active,
    ]);
})->throws(ValidationException::class);

it('audits lead conversion placeholder attempts', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $lead = CrmLead::factory()->for($company)->create();
    grantCrmPermissions($actor, ['crm_leads.convert']);
    $this->actingAs($actor);

    try {
        app(ConvertLeadToCustomer::class)->handle($lead);
    } catch (LogicException) {
        //
    }

    expect(AuditLog::query()->where('action', 'crm_lead.conversion_attempted')->where('auditable_id', $lead->id)->exists())->toBeTrue();
});

it('requires CRM permissions for actions', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $this->actingAs($actor);

    app(CreateCrmLead::class)->handle([
        'name_ar' => 'عميل محتمل',
        'status' => LeadStatus::New,
    ]);
})->throws(AuthorizationException::class);
