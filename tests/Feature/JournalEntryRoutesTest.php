<?php

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

function grantJournalRoutePermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

function createRouteJournalEntry(Company $company, string $number, JournalEntryStatus $status = JournalEntryStatus::Draft): JournalEntry
{
    [$debitAccount, $creditAccount] = Account::factory()->count(2)->for($company)->create();
    $journalEntry = JournalEntry::factory()->for($company)->create([
        'journal_number' => $number,
        'entry_date' => '2026-05-12',
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

    $journalEntry->forceFill(['status' => $status])->save();

    return $journalEntry->refresh();
}

it('stores journal entries through backend route using actions', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantJournalRoutePermissions($actor, ['journal_entries.create']);
    [$debitAccount, $creditAccount] = Account::factory()->count(2)->for($company)->create();

    $this->actingAs($actor)
        ->postJson('/journal-entries', [
            'journal_number' => 'JRN-ROUTE-001',
            'entry_date' => '2026-05-12',
            'lines' => [
                ['account_id' => $debitAccount->id, 'debit' => '100.00', 'credit' => '0.00'],
                ['account_id' => $creditAccount->id, 'debit' => '0.00', 'credit' => '100.00'],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.company_id', $company->id)
        ->assertJsonPath('data.journal_number', 'JRN-ROUTE-001');
});

it('lists journal entries with filters and tenant scope', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantJournalRoutePermissions($actor, ['journal_entries.view']);
    $matching = createRouteJournalEntry($company, 'JRN-FILTER-001', JournalEntryStatus::Posted);
    createRouteJournalEntry($company, 'JRN-FILTER-002');
    createRouteJournalEntry(Company::factory()->create(), 'JRN-FILTER-001', JournalEntryStatus::Posted);
    $accountId = $matching->lines()->firstOrFail()->account_id;

    $this->actingAs($actor)
        ->getJson("/journal-entries?status=posted&journal_number=FILTER-001&account_id={$accountId}&date_from=2026-05-01&date_to=2026-05-31")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->id);
});

it('shows and updates journal entries through thin controller', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantJournalRoutePermissions($actor, ['journal_entries.view', 'journal_entries.update']);
    $journalEntry = createRouteJournalEntry($company, 'JRN-SHOW-001');

    $this->actingAs($actor)
        ->getJson("/journal-entries/{$journalEntry->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.lines.0.account_id', $journalEntry->lines()->firstOrFail()->account_id);

    $this->actingAs($actor)
        ->patchJson("/journal-entries/{$journalEntry->id}", ['description_en' => 'Route update'])
        ->assertSuccessful()
        ->assertJsonPath('data.description_en', 'Route update');
});

it('approves posts rejects and reverses journal entries through routes', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantJournalRoutePermissions($actor, ['journal_entries.approve', 'journal_entries.post', 'journal_entries.reverse']);
    $journalEntry = createRouteJournalEntry($company, 'JRN-DECIDE-001');
    $rejectable = createRouteJournalEntry($company, 'JRN-REJECT-001');

    $this->actingAs($actor)
        ->postJson("/journal-entries/{$journalEntry->id}/approve", ['comment' => 'approved'])
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'approved');

    $this->actingAs($actor)
        ->postJson("/journal-entries/{$journalEntry->id}/post", ['comment' => 'posted'])
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'posted');

    $this->actingAs($actor)
        ->postJson("/journal-entries/{$journalEntry->id}/reverse", ['comment' => 'reversed'])
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'draft');

    $this->actingAs($actor)
        ->postJson("/journal-entries/{$rejectable->id}/reject", ['comment' => 'rejected'])
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'rejected');

    expect(AuditLog::query()->whereIn('action', [
        'journal_entry.approved',
        'journal_entry.posted',
        'journal_entry.reversed',
        'journal_entry.rejected',
    ])->count())->toBe(4);
});

it('forbids journal entry routes without permissions', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $journalEntry = createRouteJournalEntry($company, 'JRN-FORBID-001');

    $this->actingAs($actor)
        ->getJson('/journal-entries')
        ->assertForbidden();

    $this->actingAs($actor)
        ->postJson("/journal-entries/{$journalEntry->id}/post")
        ->assertForbidden();
});
