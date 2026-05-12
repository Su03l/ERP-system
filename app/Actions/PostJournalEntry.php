<?php

namespace App\Actions;

use App\Enums\JournalEntryStatus;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\JournalEntryLineValidator;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PostJournalEntry
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly JournalEntryLineValidator $lineValidator,
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(JournalEntry $journalEntry, ?User $actor = null, ?string $comment = null): JournalEntry
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($journalEntry, $actor);
        Gate::forUser($actor)->authorize('journal_entries.post');

        return DB::transaction(function () use ($actor, $comment, $journalEntry): JournalEntry {
            $this->ensurePostable($journalEntry);
            $this->lineValidator->validateEntry($journalEntry);

            $oldValues = $journalEntry->attributesToArray();

            $journalEntry->forceFill([
                'status' => JournalEntryStatus::Posted,
                'posted_by' => $actor->id,
                'posted_at' => now(),
            ])->save();

            $this->auditLogger->log(
                action: 'journal_entry.posted',
                auditable: $journalEntry,
                oldValues: $oldValues,
                newValues: $journalEntry->refresh()->attributesToArray(),
                metadata: ['comment' => $comment],
                user: $actor,
                company: $journalEntry->company_id,
            );

            return $journalEntry;
        });
    }

    /**
     * @throws ValidationException
     */
    private function ensurePostable(JournalEntry $journalEntry): void
    {
        if (! in_array($journalEntry->status, [JournalEntryStatus::Draft, JournalEntryStatus::Approved], true)) {
            throw ValidationException::withMessages([
                'status' => __('accounting.validation.journal_entries.postable_status'),
            ]);
        }

        $approvalRequired = $journalEntry->company?->accountingSetting?->accounting_approval_required ?? false;

        if ($approvalRequired && $journalEntry->status !== JournalEntryStatus::Approved) {
            throw ValidationException::withMessages([
                'status' => __('accounting.validation.journal_entries.approval_required'),
            ]);
        }
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to post journal entries.');
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
