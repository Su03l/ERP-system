<?php

use App\Actions\ApproveJournalEntry;
use App\Actions\CreateJournalEntry;
use App\Actions\PostJournalEntry;
use App\Actions\ReverseJournalEntry;
use App\Actions\UpdateJournalEntry;
use App\Enums\JournalEntryStatus;
use App\Models\Account;
use App\Models\AccountingSetting;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function grantJournalEntryPermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

function balancedJournalEntryPayload(Account $debitAccount, Account $creditAccount, array $overrides = []): array
{
    return [
        'journal_number' => $overrides['journal_number'] ?? 'JRN-ACTION-001',
        'entry_date' => $overrides['entry_date'] ?? '2026-05-12',
        'description_en' => $overrides['description_en'] ?? 'Opening entry',
        'lines' => $overrides['lines'] ?? [
            ['account_id' => $debitAccount->id, 'debit' => '250.00', 'credit' => '0.00'],
            ['account_id' => $creditAccount->id, 'debit' => '0.00', 'credit' => '250.00'],
        ],
    ];
}

function createBalancedJournalEntry(Company $company, string $number = 'JRN-BAL-001'): JournalEntry
{
    [$debitAccount, $creditAccount] = Account::factory()->count(2)->for($company)->create();
    $journalEntry = JournalEntry::factory()->for($company)->create([
        'journal_number' => $number,
        'status' => JournalEntryStatus::Draft,
    ]);

    $journalEntry->lines()->create([
        'company_id' => $company->id,
        'account_id' => $debitAccount->id,
        'debit' => '100.00',
        'credit' => '0.00',
        'line_order' => 1,
    ]);
    $journalEntry->lines()->create([
        'company_id' => $company->id,
        'account_id' => $creditAccount->id,
        'debit' => '0.00',
        'credit' => '100.00',
        'line_order' => 2,
    ]);

    return $journalEntry->refresh();
}

it('creates balanced journal entries with tenant lines and audit log', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantJournalEntryPermissions($actor, ['journal_entries.create']);
    [$debitAccount, $creditAccount] = Account::factory()->count(2)->for($company)->create();

    $this->actingAs($actor);

    $journalEntry = app(CreateJournalEntry::class)->handle(
        balancedJournalEntryPayload($debitAccount, $creditAccount),
        $actor,
    );

    expect($journalEntry->company_id)->toBe($company->id)
        ->and($journalEntry->status)->toBe(JournalEntryStatus::Draft)
        ->and($journalEntry->lines)->toHaveCount(2)
        ->and($journalEntry->lines->pluck('company_id')->unique()->all())->toBe([$company->id])
        ->and(AuditLog::query()->where('action', 'journal_entry.created')->where('auditable_id', $journalEntry->id)->exists())->toBeTrue();
});

it('prevents journal entries from using cross-company accounts', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantJournalEntryPermissions($actor, ['journal_entries.create']);
    $companyAccount = Account::factory()->for($company)->create();
    $otherAccount = Account::factory()->for(Company::factory())->create();

    $this->actingAs($actor);

    app(CreateJournalEntry::class)->handle(
        balancedJournalEntryPayload($companyAccount, $otherAccount),
        $actor,
    );
})->throws(ValidationException::class);

it('updates draft journal entries and replaces lines inside tenant scope', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantJournalEntryPermissions($actor, ['journal_entries.update']);
    $journalEntry = createBalancedJournalEntry($company);
    [$debitAccount, $creditAccount] = Account::factory()->count(2)->for($company)->create();

    $this->actingAs($actor);

    $updated = app(UpdateJournalEntry::class)->handle($journalEntry, [
        'description_en' => 'Updated entry',
        'lines' => [
            ['account_id' => $debitAccount->id, 'debit' => '375.00', 'credit' => '0.00'],
            ['account_id' => $creditAccount->id, 'debit' => '0.00', 'credit' => '375.00'],
        ],
    ], $actor);

    expect($updated->description_en)->toBe('Updated entry')
        ->and($updated->lines)->toHaveCount(2)
        ->and($updated->debitTotal())->toBe('375.00')
        ->and(AuditLog::query()->where('action', 'journal_entry.updated')->where('auditable_id', $journalEntry->id)->exists())->toBeTrue();
});

it('prevents editing posted journal entries', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantJournalEntryPermissions($actor, ['journal_entries.update']);
    $journalEntry = createBalancedJournalEntry($company);
    $journalEntry->forceFill(['status' => JournalEntryStatus::Posted])->save();

    $this->actingAs($actor);

    app(UpdateJournalEntry::class)->handle($journalEntry, ['description_en' => 'Nope'], $actor);
})->throws(ValidationException::class);

it('approves and posts balanced journal entries with audit logs', function () {
    $company = Company::factory()->create();
    AccountingSetting::factory()->for($company)->create(['accounting_approval_required' => true]);
    $actor = User::factory()->for($company)->create();
    grantJournalEntryPermissions($actor, ['journal_entries.approve', 'journal_entries.post']);
    $journalEntry = createBalancedJournalEntry($company);

    $this->actingAs($actor);

    $approved = app(ApproveJournalEntry::class)->handle($journalEntry, $actor, 'Looks correct');
    $posted = app(PostJournalEntry::class)->handle($approved, $actor, 'Post approved entry');

    expect($approved->approved_by)->toBe($actor->id)
        ->and($posted->status)->toBe(JournalEntryStatus::Posted)
        ->and($posted->posted_by)->toBe($actor->id)
        ->and(AuditLog::query()->where('action', 'journal_entry.approved')->where('auditable_id', $journalEntry->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'journal_entry.posted')->where('auditable_id', $journalEntry->id)->exists())->toBeTrue();
});

it('requires approval before posting when accounting settings require it', function () {
    $company = Company::factory()->create();
    AccountingSetting::factory()->for($company)->create(['accounting_approval_required' => true]);
    $actor = User::factory()->for($company)->create();
    grantJournalEntryPermissions($actor, ['journal_entries.post']);
    $journalEntry = createBalancedJournalEntry($company);

    $this->actingAs($actor);

    app(PostJournalEntry::class)->handle($journalEntry, $actor);
})->throws(ValidationException::class);

it('reverses posted journal entries into balanced draft reversals', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantJournalEntryPermissions($actor, ['journal_entries.reverse']);
    $journalEntry = createBalancedJournalEntry($company);
    $journalEntry->forceFill([
        'status' => JournalEntryStatus::Posted,
        'posted_by' => $actor->id,
        'posted_at' => now(),
    ])->save();

    $this->actingAs($actor);

    $reversal = app(ReverseJournalEntry::class)->handle($journalEntry, $actor, 'Correction');

    expect($reversal->company_id)->toBe($company->id)
        ->and($reversal->status)->toBe(JournalEntryStatus::Draft)
        ->and($reversal->isBalanced())->toBeTrue()
        ->and($reversal->lines)->toHaveCount(2)
        ->and($reversal->lines->first()->debit)->toBe('0.00')
        ->and($reversal->lines->first()->credit)->toBe('100.00')
        ->and(AuditLog::query()->where('action', 'journal_entry.reversed')->where('auditable_id', $journalEntry->id)->exists())->toBeTrue();
});

it('requires journal entry permissions for actions', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    [$debitAccount, $creditAccount] = Account::factory()->count(2)->for($company)->create();

    $this->actingAs($actor);

    app(CreateJournalEntry::class)->handle(
        balancedJournalEntryPayload($debitAccount, $creditAccount),
        $actor,
    );
})->throws(AuthorizationException::class);
