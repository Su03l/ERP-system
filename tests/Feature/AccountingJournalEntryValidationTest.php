<?php

use App\Enums\JournalEntryStatus;
use App\Http\Requests\ApproveJournalEntryRequest;
use App\Http\Requests\PostJournalEntryRequest;
use App\Http\Requests\StoreJournalEntryRequest;
use App\Http\Requests\UpdateJournalEntryRequest;
use App\Models\Account;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Route::post('/test/accounting/journal-entries', fn (StoreJournalEntryRequest $request) => $request->validated());
    Route::patch('/test/accounting/journal-entries/{journalEntry}', fn (UpdateJournalEntryRequest $request, JournalEntry $journalEntry) => $request->validated());
    Route::post('/test/accounting/journal-entries/{journalEntry}/post', fn (PostJournalEntryRequest $request, JournalEntry $journalEntry) => $request->validated());
    Route::post('/test/accounting/journal-entries/{journalEntry}/approve', fn (ApproveJournalEntryRequest $request, JournalEntry $journalEntry) => $request->validated());
});

it('validates journal entry lines are balanced and tenant scoped', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $asset = Account::factory()->for($company)->create();
    $otherCompanyAccount = Account::factory()->for(Company::factory())->create();

    $this->actingAs($actor)
        ->postJson('/test/accounting/journal-entries', [
            'journal_number' => 'JRN-2026-001',
            'entry_date' => '2026-05-12',
            'lines' => [
                ['account_id' => $asset->id, 'debit' => '100.00', 'credit' => '0.00'],
                ['account_id' => $otherCompanyAccount->id, 'debit' => '0.00', 'credit' => '90.00'],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['lines', 'lines.1.account_id']);
});

it('allows storing a balanced tenant-owned journal entry payload', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    [$debitAccount, $creditAccount] = Account::factory()->count(2)->for($company)->create();

    $this->actingAs($actor)
        ->postJson('/test/accounting/journal-entries', [
            'journal_number' => 'JRN-2026-002',
            'entry_date' => '2026-05-12',
            'lines' => [
                ['account_id' => $debitAccount->id, 'debit' => '150.50', 'credit' => '0.00'],
                ['account_id' => $creditAccount->id, 'debit' => '0.00', 'credit' => '150.50'],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('journal_number', 'JRN-2026-002');
});

it('prevents editing non-draft journal entries', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $journalEntry = JournalEntry::factory()->for($company)->create([
        'status' => JournalEntryStatus::Posted,
    ]);

    $this->actingAs($actor)
        ->patchJson("/test/accounting/journal-entries/{$journalEntry->id}", [
            'description_en' => 'Updated description',
        ])
        ->assertForbidden();
});

it('requires balanced journal entries before posting or approving', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $journalEntry = JournalEntry::factory()->for($company)->create();
    $account = Account::factory()->for($company)->create();

    $journalEntry->lines()->create([
        'company_id' => $company->id,
        'account_id' => $account->id,
        'debit' => '100.00',
        'credit' => '0.00',
    ]);

    $this->actingAs($actor)
        ->postJson("/test/accounting/journal-entries/{$journalEntry->id}/post")
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['lines']);

    $this->actingAs($actor)
        ->postJson("/test/accounting/journal-entries/{$journalEntry->id}/approve")
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['lines']);
});
