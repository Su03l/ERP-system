<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Validation\ValidationException;

class JournalEntryLineValidator
{
    /**
     * @param  array<int, array<string, mixed>>  $lines
     *
     * @throws ValidationException
     */
    public function validateLines(array $lines, int $companyId): void
    {
        if (count($lines) < 2) {
            throw ValidationException::withMessages([
                'lines' => __('validation.min.array', ['attribute' => 'lines', 'min' => 2]),
            ]);
        }

        $this->ensureAccountsBelongToCompany($lines, $companyId);
        $this->ensureLinesBalance($lines);
    }

    /**
     * @throws ValidationException
     */
    public function validateEntry(JournalEntry $journalEntry): void
    {
        if (! $journalEntry->isBalanced()) {
            throw ValidationException::withMessages([
                'lines' => __('accounting.validation.journal_entries.unbalanced'),
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     *
     * @throws ValidationException
     */
    private function ensureAccountsBelongToCompany(array $lines, int $companyId): void
    {
        $accountIds = collect($lines)
            ->pluck('account_id')
            ->filter()
            ->unique()
            ->values();

        if ($accountIds->isEmpty()) {
            throw ValidationException::withMessages([
                'lines' => __('validation.required', ['attribute' => 'account_id']),
            ]);
        }

        $tenantAccountCount = Account::query()
            ->where('company_id', $companyId)
            ->whereKey($accountIds)
            ->count();

        if ($tenantAccountCount !== $accountIds->count()) {
            throw ValidationException::withMessages([
                'lines.*.account_id' => __('validation.exists', ['attribute' => 'account_id']),
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     *
     * @throws ValidationException
     */
    private function ensureLinesBalance(array $lines): void
    {
        $debitTotal = 0;
        $creditTotal = 0;

        foreach ($lines as $index => $line) {
            $debit = $this->toCents($line['debit'] ?? 0);
            $credit = $this->toCents($line['credit'] ?? 0);

            if ($debit > 0 && $credit > 0) {
                throw ValidationException::withMessages([
                    "lines.{$index}.debit" => __('accounting.validation.journal_entries.single_side'),
                ]);
            }

            if ($debit === 0 && $credit === 0) {
                throw ValidationException::withMessages([
                    "lines.{$index}.debit" => __('accounting.validation.journal_entries.non_zero_line'),
                ]);
            }

            $debitTotal += $debit;
            $creditTotal += $credit;
        }

        if ($debitTotal !== $creditTotal) {
            throw ValidationException::withMessages([
                'lines' => __('accounting.validation.journal_entries.unbalanced'),
            ]);
        }
    }

    private function toCents(mixed $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }
}
