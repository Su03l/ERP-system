<?php

use App\Enums\AccountNormalBalance;
use App\Enums\AccountType;
use App\Enums\JournalEntryStatus;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantAccountPermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

function accountPayload(array $overrides = []): array
{
    return [
        'code' => $overrides['code'] ?? '1000',
        'name_ar' => $overrides['name_ar'] ?? 'النقدية',
        'name_en' => $overrides['name_en'] ?? 'Cash',
        'type' => $overrides['type'] ?? AccountType::Asset->value,
        'normal_balance' => $overrides['normal_balance'] ?? AccountNormalBalance::Debit->value,
        'metadata' => $overrides['metadata'] ?? ['source' => 'test'],
    ] + $overrides;
}

it('creates accounts through thin backend route with tenant ownership', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantAccountPermissions($actor, ['accounts.create']);

    $this->actingAs($actor)
        ->postJson('/accounts', accountPayload())
        ->assertSuccessful()
        ->assertJsonPath('data.company_id', $company->id)
        ->assertJsonPath('data.code', '1000');

    $account = Account::query()->firstOrFail();

    expect($account->company_id)->toBe($company->id)
        ->and($account->is_active)->toBeTrue()
        ->and(AuditLog::query()->where('action', 'account.created')->where('auditable_id', $account->id)->exists())->toBeTrue();
});

it('validates account parent and code inside the current company', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantAccountPermissions($actor, ['accounts.create']);
    Account::factory()->for($company)->create(['code' => '1000']);
    $otherParent = Account::factory()->for(Company::factory())->create();

    $this->actingAs($actor)
        ->postJson('/accounts', accountPayload([
            'parent_id' => $otherParent->id,
            'code' => '1000',
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['parent_id', 'code']);
});

it('lists and filters accounts for the current company only', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantAccountPermissions($actor, ['accounts.view']);
    Account::factory()->for($company)->create(['code' => '1000', 'name_en' => 'Cash', 'type' => AccountType::Asset]);
    Account::factory()->for($company)->create(['code' => '4000', 'name_en' => 'Revenue', 'type' => AccountType::Revenue]);
    Account::factory()->for(Company::factory())->create(['code' => '1000', 'name_en' => 'Other Cash', 'type' => AccountType::Asset]);

    $this->actingAs($actor)
        ->getJson('/accounts?type=asset&search=Cash')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.code', '1000');
});

it('updates accounts and rejects self parent assignment', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantAccountPermissions($actor, ['accounts.update']);
    $account = Account::factory()->for($company)->create(['code' => '1000']);

    $this->actingAs($actor)
        ->patchJson("/accounts/{$account->id}", ['parent_id' => $account->id])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['parent_id']);

    $this->actingAs($actor)
        ->patchJson("/accounts/{$account->id}", ['name_en' => 'Updated cash'])
        ->assertSuccessful()
        ->assertJsonPath('data.name_en', 'Updated cash');

    expect(AuditLog::query()->where('action', 'account.updated')->where('auditable_id', $account->id)->exists())->toBeTrue();
});

it('archives accounts without posted journal lines', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantAccountPermissions($actor, ['accounts.delete']);
    $account = Account::factory()->for($company)->create(['is_active' => true]);

    $this->actingAs($actor)
        ->deleteJson("/accounts/{$account->id}")
        ->assertNoContent();

    expect($account->refresh()->is_active)->toBeFalse()
        ->and(AuditLog::query()->where('action', 'account.archived')->where('auditable_id', $account->id)->exists())->toBeTrue();
});

it('prevents archiving accounts with posted journal lines', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantAccountPermissions($actor, ['accounts.delete']);
    $account = Account::factory()->for($company)->create();
    $journalEntry = JournalEntry::factory()->for($company)->create(['status' => JournalEntryStatus::Posted]);
    $journalEntry->lines()->create([
        'company_id' => $company->id,
        'account_id' => $account->id,
        'debit' => '100.00',
        'credit' => '100.00',
    ]);

    $this->actingAs($actor)
        ->deleteJson("/accounts/{$account->id}")
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['account']);
});
