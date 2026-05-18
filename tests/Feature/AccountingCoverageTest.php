<?php

use App\Actions\CreateJournalEntry;
use App\Actions\PostJournalEntry;
use App\Enums\AccountNormalBalance;
use App\Enums\AccountType;
use App\Enums\JournalEntryStatus;
use App\Enums\PaymentDirection;
use App\Enums\PaymentStatus;
use App\Models\Account;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\FinancialReportQueryService;
use App\Services\SalesInvoiceCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function grantAccountingCoveragePermissions214(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::query()->firstOrCreate(
            ['key' => $permissionKey],
            ['name' => $permissionKey, 'description' => null],
        );

        $role->permissions()->syncWithoutDetaching([$permission->id]);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

function accountingCoverageJournalPayload214(Account $debitAccount, Account $creditAccount, string $amount = '250.00'): array
{
    return [
        'journal_number' => 'JRN-214-001',
        'entry_date' => '2026-05-12',
        'description_en' => 'Coverage entry',
        'lines' => [
            ['account_id' => $debitAccount->id, 'debit' => $amount, 'credit' => '0.00'],
            ['account_id' => $creditAccount->id, 'debit' => '0.00', 'credit' => $amount],
        ],
    ];
}

test('account creation is tenant scoped and duplicate account codes are rejected', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantAccountingCoveragePermissions214($actor, ['accounts.create']);

    $this->actingAs($actor)
        ->postJson('/accounts', [
            'code' => '1000',
            'name_ar' => 'Cash',
            'name_en' => 'Cash',
            'type' => AccountType::Asset->value,
            'normal_balance' => AccountNormalBalance::Debit->value,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.company_id', $company->id);

    $this->actingAs($actor)
        ->postJson('/accounts', [
            'code' => '1000',
            'name_ar' => 'Cash duplicate',
            'type' => AccountType::Asset->value,
            'normal_balance' => AccountNormalBalance::Debit->value,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['code']);
});

test('journal entries must balance before posting', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $cash = Account::factory()->for($company)->create(['type' => AccountType::Asset]);
    $revenue = Account::factory()->for($company)->create(['type' => AccountType::Revenue]);
    grantAccountingCoveragePermissions214($actor, ['journal_entries.create', 'journal_entries.post']);

    $this->actingAs($actor);

    app(CreateJournalEntry::class)->handle([
        ...accountingCoverageJournalPayload214($cash, $revenue),
        'journal_number' => 'JRN-214-BAD',
        'lines' => [
            ['account_id' => $cash->id, 'debit' => '300.00', 'credit' => '0.00'],
            ['account_id' => $revenue->id, 'debit' => '0.00', 'credit' => '200.00'],
        ],
    ], $actor);
})->throws(ValidationException::class);

test('balanced journal entries post and financial reports use posted entries only', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $cash = Account::factory()->for($company)->create([
        'code' => '1000',
        'name_en' => 'Cash',
        'type' => AccountType::Asset,
        'normal_balance' => AccountNormalBalance::Debit,
    ]);
    $revenue = Account::factory()->for($company)->create([
        'code' => '4000',
        'name_en' => 'Revenue',
        'type' => AccountType::Revenue,
        'normal_balance' => AccountNormalBalance::Credit,
    ]);
    grantAccountingCoveragePermissions214($actor, [
        'journal_entries.create',
        'journal_entries.post',
        'financial_reports.view',
    ]);

    $this->actingAs($actor);

    $entry = app(CreateJournalEntry::class)->handle(accountingCoverageJournalPayload214($cash, $revenue), $actor);
    $posted = app(PostJournalEntry::class)->handle($entry, $actor);

    $draft = JournalEntry::factory()->for($company)->create(['status' => JournalEntryStatus::Draft]);
    $draft->lines()->create(['company_id' => $company->id, 'account_id' => $cash->id, 'debit' => '999.00', 'credit' => '0.00']);

    app()->setLocale('en');

    $trialBalance = app(FinancialReportQueryService::class)->trialBalance(actor: $actor);

    expect($posted->status)->toBe(JournalEntryStatus::Posted)
        ->and($trialBalance)->toHaveCount(2)
        ->and($trialBalance[0]['name'])->toBe('Cash')
        ->and($trialBalance[0]['debit'])->toBe('250.00')
        ->and($trialBalance[1]['credit'])->toBe('250.00');
});

test('invoice totals are recalculated server side and payments stay tenant scoped', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    $totals = app(SalesInvoiceCalculationService::class)->calculate([
        [
            'description_ar' => 'Service',
            'quantity' => '2',
            'unit_price' => '100.00',
            'discount_amount' => '10.00',
            'tax_rate' => '15',
        ],
        [
            'description_ar' => 'Support',
            'quantity' => '1',
            'unit_price' => '50.00',
            'discount_amount' => '0.00',
            'tax_rate' => '0',
        ],
    ], '50.00');

    $payment = Payment::factory()->for($company)->create([
        'direction' => PaymentDirection::Incoming,
        'status' => PaymentStatus::Completed,
        'amount' => '125.00',
    ]);
    Payment::factory()->for($otherCompany)->create();

    $this->actingAs($user);

    expect($totals['subtotal'])->toBe('250.00')
        ->and($totals['tax_amount'])->toBe('28.50')
        ->and($totals['total_amount'])->toBe('268.50')
        ->and($totals['balance_due'])->toBe('218.50')
        ->and(Payment::query()->forCurrentCompany()->pluck('id')->all())->toBe([$payment->id]);
});
