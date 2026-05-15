<?php

use App\DTOs\KpiDateRange;
use App\Enums\AccountNormalBalance;
use App\Enums\AccountType;
use App\Enums\InvoiceStatus;
use App\Enums\JournalEntryStatus;
use App\Enums\PaymentDirection;
use App\Enums\PaymentStatus;
use App\Models\Account;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Services\Kpis\Accounting\CashFlowKpi;
use App\Services\Kpis\Accounting\ExpensesKpi;
use App\Services\Kpis\Accounting\NetProfitKpi;
use App\Services\Kpis\Accounting\OverdueInvoicesKpi;
use App\Services\Kpis\Accounting\PayablesKpi;
use App\Services\Kpis\Accounting\ReceivablesKpi;
use App\Services\Kpis\Accounting\RevenueKpi;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves accounting KPI values from posted entries invoices and payments', function () {
    $company = Company::factory()->create();
    $entry = JournalEntry::factory()->for($company)->create([
        'status' => JournalEntryStatus::Posted,
        'entry_date' => '2026-01-10',
    ]);
    $revenueAccount = Account::factory()->for($company)->create(['type' => AccountType::Revenue, 'normal_balance' => AccountNormalBalance::Credit]);
    $expenseAccount = Account::factory()->for($company)->create(['type' => AccountType::Expense, 'normal_balance' => AccountNormalBalance::Debit]);
    JournalEntryLine::factory()->for($company)->for($entry, 'journalEntry')->for($revenueAccount, 'account')->create(['credit' => 1000, 'debit' => 0]);
    JournalEntryLine::factory()->for($company)->for($entry, 'journalEntry')->for($expenseAccount, 'account')->create(['debit' => 300, 'credit' => 0]);
    SalesInvoice::factory()->for($company)->create(['invoice_date' => '2026-01-11', 'due_date' => now()->subDay(), 'status' => InvoiceStatus::Sent, 'balance_due' => 400]);
    PurchaseInvoice::factory()->for($company)->create(['invoice_date' => '2026-01-12', 'status' => InvoiceStatus::Sent, 'balance_due' => 250]);
    Payment::factory()->for($company)->create(['payment_date' => '2026-01-13', 'status' => PaymentStatus::Completed, 'direction' => PaymentDirection::Incoming, 'amount' => 700]);
    Payment::factory()->for($company)->create(['payment_date' => '2026-01-14', 'status' => PaymentStatus::Completed, 'direction' => PaymentDirection::Outgoing, 'amount' => 200]);

    $range = KpiDateRange::fromDates('2026-01-01', '2026-01-31');

    expect(app(RevenueKpi::class)->resolve($company, $range)->value)->toBe(1000.0)
        ->and(app(ExpensesKpi::class)->resolve($company, $range)->value)->toBe(300.0)
        ->and(app(NetProfitKpi::class)->resolve($company, $range)->value)->toBe(700.0)
        ->and(app(ReceivablesKpi::class)->resolve($company, $range)->value)->toBe(400.0)
        ->and(app(PayablesKpi::class)->resolve($company, $range)->value)->toBe(250.0)
        ->and(app(CashFlowKpi::class)->resolve($company, $range)->value)->toBe(500.0)
        ->and(app(OverdueInvoicesKpi::class)->resolve($company, $range)->value)->toBe(1);
});
