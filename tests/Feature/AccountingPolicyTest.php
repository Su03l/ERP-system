<?php

use App\Models\Account;
use App\Models\AccountingSetting;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

function grantAccountingPermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('authorizes accounting settings permissions by company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $setting = AccountingSetting::factory()->for($company)->create();
    $otherSetting = AccountingSetting::factory()->for(Company::factory())->create();
    grantAccountingPermissions($user, ['accounting_settings.view', 'accounting_settings.update']);

    expect(Gate::forUser($user)->allows('viewAny', AccountingSetting::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('view', $setting))->toBeTrue()
        ->and(Gate::forUser($user)->allows('update', $setting))->toBeTrue()
        ->and(Gate::forUser($user)->denies('view', $otherSetting))->toBeTrue()
        ->and(Gate::forUser($user)->denies('update', $otherSetting))->toBeTrue();
});

it('authorizes account management permissions without role names', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $account = Account::factory()->for($company)->create();
    $otherAccount = Account::factory()->for(Company::factory())->create();
    grantAccountingPermissions($user, ['accounts.view', 'accounts.create', 'accounts.update', 'accounts.delete']);

    expect(Gate::forUser($user)->allows('viewAny', Account::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('create', Account::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('view', $account))->toBeTrue()
        ->and(Gate::forUser($user)->allows('update', $account))->toBeTrue()
        ->and(Gate::forUser($user)->allows('delete', $account))->toBeTrue()
        ->and(Gate::forUser($user)->denies('view', $otherAccount))->toBeTrue()
        ->and(Gate::forUser($user)->denies('delete', $otherAccount))->toBeTrue();
});

it('requires explicit journal entry permissions for financial actions', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $journalEntry = JournalEntry::factory()->for($company)->create();
    $otherJournalEntry = JournalEntry::factory()->for(Company::factory())->create();
    grantAccountingPermissions($user, [
        'journal_entries.view',
        'journal_entries.create',
        'journal_entries.update',
        'journal_entries.post',
        'journal_entries.approve',
        'journal_entries.reverse',
    ]);

    expect(Gate::forUser($user)->allows('viewAny', JournalEntry::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('create', JournalEntry::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('view', $journalEntry))->toBeTrue()
        ->and(Gate::forUser($user)->allows('update', $journalEntry))->toBeTrue()
        ->and(Gate::forUser($user)->allows('post', $journalEntry))->toBeTrue()
        ->and(Gate::forUser($user)->allows('approve', $journalEntry))->toBeTrue()
        ->and(Gate::forUser($user)->allows('reverse', $journalEntry))->toBeTrue()
        ->and(Gate::forUser($user)->denies('post', $otherJournalEntry))->toBeTrue()
        ->and(Gate::forUser($user)->denies('approve', $otherJournalEntry))->toBeTrue();
});

it('supports financial report view and export gates', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantAccountingPermissions($user, ['financial_reports.view', 'financial_reports.export']);

    expect(Gate::forUser($user)->allows('financial_reports.view'))->toBeTrue()
        ->and(Gate::forUser($user)->allows('financial_reports.export'))->toBeTrue();
});
