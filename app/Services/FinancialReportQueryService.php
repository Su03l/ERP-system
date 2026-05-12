<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Enums\JournalEntryStatus;
use App\Models\Account;
use App\Models\JournalEntryLine;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class FinancialReportQueryService
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    /**
     * @param  array{date_from?: string, date_to?: string, account_id?: int, include_drafts?: bool}  $filters
     * @return array<int, array<string, mixed>>
     */
    public function trialBalance(array $filters = [], ?User $actor = null): array
    {
        $companyId = $this->authorize($actor);

        return $this->lineQuery($companyId, $filters)
            ->with('account')
            ->get()
            ->groupBy('account_id')
            ->map(fn (Collection $lines): array => $this->accountSummary($lines->first()->account, $lines))
            ->values()
            ->all();
    }

    /**
     * @param  array{date_from?: string, date_to?: string, account_id?: int, include_drafts?: bool}  $filters
     * @return array<int, array<string, mixed>>
     */
    public function generalLedger(array $filters = [], ?User $actor = null): array
    {
        $companyId = $this->authorize($actor);

        return $this->lineQuery($companyId, $filters)
            ->with(['account', 'journalEntry'])
            ->orderBy(JournalEntryLine::query()->getModel()->getTable().'.id')
            ->get()
            ->map(fn (JournalEntryLine $line): array => $this->ledgerLine($line))
            ->all();
    }

    /**
     * @param  array{date_from?: string, date_to?: string, include_drafts?: bool}  $filters
     * @return array{account: array<string, mixed>, lines: array<int, array<string, mixed>>, totals: array<string, string>}
     */
    public function accountStatement(Account $account, array $filters = [], ?User $actor = null): array
    {
        $companyId = $this->authorize($actor);

        if ($account->company_id !== $companyId) {
            throw new AuthorizationException('Account does not belong to the current company.');
        }

        $lines = $this->lineQuery($companyId, [...$filters, 'account_id' => $account->id])
            ->with(['account', 'journalEntry'])
            ->orderBy(JournalEntryLine::query()->getModel()->getTable().'.id')
            ->get();

        return [
            'account' => $this->accountPayload($account),
            'lines' => $lines->map(fn (JournalEntryLine $line): array => $this->ledgerLine($line))->all(),
            'totals' => $this->totals($lines),
        ];
    }

    /** @return array<string, mixed> */
    public function incomeStatement(array $filters = [], ?User $actor = null): array
    {
        return $this->typedStatement([AccountType::Revenue, AccountType::Expense], $filters, $actor);
    }

    /** @return array<string, mixed> */
    public function balanceSheet(array $filters = [], ?User $actor = null): array
    {
        return $this->typedStatement([AccountType::Asset, AccountType::Liability, AccountType::Equity], $filters, $actor);
    }

    /**
     * @param  array<int, AccountType>  $types
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function typedStatement(array $types, array $filters, ?User $actor): array
    {
        $companyId = $this->authorize($actor);
        $rows = $this->lineQuery($companyId, $filters)
            ->whereHas('account', fn (Builder $query) => $query->whereIn('type', array_map(fn (AccountType $type): string => $type->value, $types)))
            ->with('account')
            ->get()
            ->groupBy('account_id')
            ->map(fn (Collection $lines): array => $this->accountSummary($lines->first()->account, $lines))
            ->values()
            ->all();

        return [
            'rows' => $rows,
            'totals' => [
                'debit' => $this->money(collect($rows)->sum(fn (array $row): int => $this->toCents($row['debit']))),
                'credit' => $this->money(collect($rows)->sum(fn (array $row): int => $this->toCents($row['credit']))),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<JournalEntryLine>
     */
    private function lineQuery(int $companyId, array $filters): Builder
    {
        return JournalEntryLine::query()
            ->where('company_id', $companyId)
            ->whereHas('journalEntry', function (Builder $query) use ($filters): void {
                if (($filters['include_drafts'] ?? false) !== true) {
                    $query->where('status', JournalEntryStatus::Posted);
                }

                $query
                    ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('entry_date', '>=', $date))
                    ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('entry_date', '<=', $date));
            })
            ->when($filters['account_id'] ?? null, fn (Builder $query, int $accountId) => $query->where('account_id', $accountId));
    }

    /** @param Collection<int, JournalEntryLine> $lines */
    private function accountSummary(Account $account, Collection $lines): array
    {
        return [
            ...$this->accountPayload($account),
            ...$this->totals($lines),
        ];
    }

    /** @return array<string, mixed> */
    private function accountPayload(Account $account): array
    {
        return [
            'account_id' => $account->id,
            'code' => $account->code,
            'name' => app()->getLocale() === 'en' && $account->name_en !== null ? $account->name_en : $account->name_ar,
            'type' => $account->type?->value,
            'normal_balance' => $account->normal_balance?->value,
        ];
    }

    private function ledgerLine(JournalEntryLine $line): array
    {
        return [
            'journal_entry_id' => $line->journal_entry_id,
            'journal_number' => $line->journalEntry?->journal_number,
            'entry_date' => $line->journalEntry?->entry_date?->toDateString(),
            'description_ar' => $line->description_ar ?? $line->journalEntry?->description_ar,
            'description_en' => $line->description_en ?? $line->journalEntry?->description_en,
            'account' => $this->accountPayload($line->account),
            'debit' => $line->debit,
            'credit' => $line->credit,
        ];
    }

    /** @param Collection<int, JournalEntryLine> $lines */
    private function totals(Collection $lines): array
    {
        $debit = $lines->sum(fn (JournalEntryLine $line): int => $this->toCents($line->debit));
        $credit = $lines->sum(fn (JournalEntryLine $line): int => $this->toCents($line->credit));

        return [
            'debit' => $this->money($debit),
            'credit' => $this->money($credit),
            'balance' => $this->money($debit - $credit),
        ];
    }

    private function authorize(?User $actor): int
    {
        $actor ??= Auth::user();
        $companyId = $this->tenantContext->companyId();

        if (! $actor instanceof User || $companyId === null || $actor->company_id !== $companyId || ! $actor->hasPermission('financial_reports.view', $companyId)) {
            throw new AuthorizationException('You are not authorized to view financial reports.');
        }

        return $companyId;
    }

    private function toCents(string|int|float|null $amount): int
    {
        return (int) round(((float) ($amount ?? 0)) * 100);
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
