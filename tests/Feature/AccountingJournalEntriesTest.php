<?php

use App\Enums\AccountNormalBalance;
use App\Enums\AccountType;
use App\Enums\InvoiceStatus;
use App\Enums\JournalEntrySource;
use App\Enums\JournalEntryStatus;
use App\Enums\PaymentStatus;
use App\Models\Account;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('provides localized labels for accounting workflow enums', function () {
    app()->setLocale('ar');

    expect(JournalEntryStatus::Posted->label())->toBe('مرحل')
        ->and(JournalEntrySource::Payroll->label())->toBe('الرواتب')
        ->and(InvoiceStatus::PartiallyPaid->label())->toBe('مدفوعة جزئيا')
        ->and(PaymentStatus::Completed->label())->toBe('مكتملة')
        ->and(AccountType::Revenue->label())->toBe('إيراد')
        ->and(AccountNormalBalance::Credit->label())->toBe('دائن');
});

it('stores tenant scoped journal entries and balanced lines', function () {
    $company = Company::factory()->create();
    $postedBy = User::factory()->for($company)->create();
    $cash = Account::factory()->for($company)->create([
        'code' => '1000',
        'type' => AccountType::Asset,
        'normal_balance' => AccountNormalBalance::Debit,
    ]);
    $revenue = Account::factory()->for($company)->create([
        'code' => '4000',
        'type' => AccountType::Revenue,
        'normal_balance' => AccountNormalBalance::Credit,
    ]);
    $entry = JournalEntry::factory()->for($company)->create([
        'journal_number' => 'JRN-0001',
        'entry_date' => '2026-05-12',
        'source' => JournalEntrySource::Manual,
        'status' => JournalEntryStatus::Draft,
        'posted_by' => $postedBy->id,
    ]);

    JournalEntryLine::factory()->for($company)->create([
        'journal_entry_id' => $entry->id,
        'account_id' => $cash->id,
        'debit' => 1000,
        'credit' => 0,
        'line_order' => 1,
    ]);
    JournalEntryLine::factory()->for($company)->create([
        'journal_entry_id' => $entry->id,
        'account_id' => $revenue->id,
        'debit' => 0,
        'credit' => 1000,
        'line_order' => 2,
    ]);

    expect($company->journalEntries()->whereKey($entry)->exists())->toBeTrue()
        ->and($entry->lines)->toHaveCount(2)
        ->and($entry->debitTotal())->toBe('1000.00')
        ->and($entry->creditTotal())->toBe('1000.00')
        ->and($entry->isBalanced())->toBeTrue()
        ->and($entry->status)->toBe(JournalEntryStatus::Draft)
        ->and($entry->source)->toBe(JournalEntrySource::Manual)
        ->and($entry->postedBy->is($postedBy))->toBeTrue();
});

it('prevents posting unbalanced journal entries', function () {
    $company = Company::factory()->create();
    $account = Account::factory()->for($company)->create();
    $entry = JournalEntry::factory()->for($company)->create();

    JournalEntryLine::factory()->for($company)->create([
        'journal_entry_id' => $entry->id,
        'account_id' => $account->id,
        'debit' => 100,
        'credit' => 0,
    ]);

    $entry->status = JournalEntryStatus::Posted;
    $entry->save();
})->throws(ValidationException::class);
