<?php

use App\Enums\AccountNormalBalance;
use App\Enums\AccountType;
use App\Enums\JournalEntryStatus;
use App\Models\Account;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\FinancialReportQueryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantFinancialReportsView(User $user): void
{
    $role = Role::factory()->for($user->company)->create();
    $role->permissions()->attach(Permission::factory()->create(['key' => 'financial_reports.view']));
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

function postedEntry(Company $company, Account $debitAccount, Account $creditAccount, string $date = '2026-05-12'): JournalEntry
{
    $entry = JournalEntry::factory()->for($company)->create([
        'entry_date' => $date,
        'status' => JournalEntryStatus::Draft,
    ]);
    $entry->lines()->create(['company_id' => $company->id, 'account_id' => $debitAccount->id, 'debit' => '100.00', 'credit' => '0.00']);
    $entry->lines()->create(['company_id' => $company->id, 'account_id' => $creditAccount->id, 'debit' => '0.00', 'credit' => '100.00']);
    $entry->forceFill(['status' => JournalEntryStatus::Posted])->save();

    return $entry;
}

it('builds trial balance from posted journal entries only', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantFinancialReportsView($actor);
    $cash = Account::factory()->for($company)->create(['code' => '1000', 'name_en' => 'Cash', 'type' => AccountType::Asset, 'normal_balance' => AccountNormalBalance::Debit]);
    $revenue = Account::factory()->for($company)->create(['code' => '4000', 'name_en' => 'Revenue', 'type' => AccountType::Revenue, 'normal_balance' => AccountNormalBalance::Credit]);
    postedEntry($company, $cash, $revenue);
    $draft = JournalEntry::factory()->for($company)->create(['status' => JournalEntryStatus::Draft]);
    $draft->lines()->create(['company_id' => $company->id, 'account_id' => $cash->id, 'debit' => '999.00', 'credit' => '0.00']);

    $this->actingAs($actor);
    app()->setLocale('en');

    $rows = app(FinancialReportQueryService::class)->trialBalance(actor: $actor);

    expect($rows)->toHaveCount(2)
        ->and($rows[0]['name'])->toBe('Cash')
        ->and($rows[0]['debit'])->toBe('100.00')
        ->and($rows[1]['credit'])->toBe('100.00');
});

it('builds general ledger and account statement with filters', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantFinancialReportsView($actor);
    $cash = Account::factory()->for($company)->create();
    $revenue = Account::factory()->for($company)->create(['type' => AccountType::Revenue]);
    postedEntry($company, $cash, $revenue, '2026-05-12');
    postedEntry($company, $cash, $revenue, '2026-06-01');

    $this->actingAs($actor);

    $ledger = app(FinancialReportQueryService::class)->generalLedger(['date_to' => '2026-05-31'], $actor);
    $statement = app(FinancialReportQueryService::class)->accountStatement($cash, ['date_to' => '2026-05-31'], $actor);

    expect($ledger)->toHaveCount(2)
        ->and($statement['lines'])->toHaveCount(1)
        ->and($statement['totals']['debit'])->toBe('100.00');
});

it('returns real typed statement rows and protects tenant reports', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantFinancialReportsView($actor);
    $cash = Account::factory()->for($company)->create(['type' => AccountType::Asset]);
    $revenue = Account::factory()->for($company)->create(['type' => AccountType::Revenue]);
    postedEntry($company, $cash, $revenue);

    $this->actingAs($actor);

    expect(app(FinancialReportQueryService::class)->incomeStatement(actor: $actor)['rows'])->toHaveCount(1)
        ->and(app(FinancialReportQueryService::class)->balanceSheet(actor: $actor)['rows'])->toHaveCount(1);

    $otherAccount = Account::factory()->for(Company::factory())->create();
    app(FinancialReportQueryService::class)->accountStatement($otherAccount, actor: $actor);
})->throws(AuthorizationException::class);
