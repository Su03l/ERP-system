<?php

namespace App\Actions;

use App\Enums\JournalEntrySource;
use App\Enums\JournalEntryStatus;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ReverseJournalEntry
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(JournalEntry $journalEntry, ?User $actor = null, ?string $comment = null): JournalEntry
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($journalEntry, $actor);
        Gate::forUser($actor)->authorize('journal_entries.reverse');

        return DB::transaction(function () use ($actor, $comment, $journalEntry): JournalEntry {
            $this->ensurePosted($journalEntry);
            $journalEntry->load('lines');

            $reversal = JournalEntry::create([
                'company_id' => $journalEntry->company_id,
                'journal_number' => "REV-{$journalEntry->journal_number}-{$journalEntry->id}",
                'entry_date' => now()->toDateString(),
                'description_ar' => $journalEntry->description_ar,
                'description_en' => $journalEntry->description_en,
                'source' => JournalEntrySource::Adjustment,
                'source_type' => $journalEntry->getMorphClass(),
                'source_id' => $journalEntry->id,
                'status' => JournalEntryStatus::Draft,
                'metadata' => [
                    'reverses_journal_entry_id' => $journalEntry->id,
                    'comment' => $comment,
                ],
            ]);

            foreach ($journalEntry->lines as $line) {
                $reversal->lines()->create([
                    'company_id' => $reversal->company_id,
                    'account_id' => $line->account_id,
                    'description_ar' => $line->description_ar,
                    'description_en' => $line->description_en,
                    'debit' => $line->credit,
                    'credit' => $line->debit,
                    'line_order' => $line->line_order,
                    'metadata' => [
                        'reverses_journal_entry_line_id' => $line->id,
                    ],
                ]);
            }

            $this->auditLogger->log(
                action: 'journal_entry.reversed',
                auditable: $journalEntry,
                newValues: $reversal->refresh()->load('lines')->attributesToArray(),
                metadata: ['comment' => $comment, 'reversal_journal_entry_id' => $reversal->id],
                user: $actor,
                company: $journalEntry->company_id,
            );

            return $reversal;
        });
    }

    /**
     * @throws ValidationException
     */
    private function ensurePosted(JournalEntry $journalEntry): void
    {
        if ($journalEntry->status !== JournalEntryStatus::Posted) {
            throw ValidationException::withMessages([
                'status' => __('accounting.validation.journal_entries.reversible_status'),
            ]);
        }
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to reverse journal entries.');
        }

        return $actor;
    }

    private function ensureTenant(JournalEntry $journalEntry, User $actor): void
    {
        if ($this->tenantContext->companyId() !== $journalEntry->company_id || $actor->company_id !== $journalEntry->company_id) {
            throw new AuthorizationException('Journal entry does not belong to the current company.');
        }
    }
}
