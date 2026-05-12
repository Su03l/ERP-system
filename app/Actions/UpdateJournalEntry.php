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

class UpdateJournalEntry
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly JournalEntryLineValidator $lineValidator,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(JournalEntry $journalEntry, array $data, ?User $actor = null): JournalEntry
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($journalEntry, $actor);
        Gate::forUser($actor)->authorize('journal_entries.update');
        $this->ensureDraft($journalEntry);

        $lines = $data['lines'] ?? null;
        unset($data['lines'], $data['company_id'], $data['status'], $data['posted_by'], $data['posted_at'], $data['approved_by'], $data['approved_at']);

        if (is_array($lines)) {
            $this->lineValidator->validateLines($lines, $journalEntry->company_id);
        }

        return DB::transaction(function () use ($actor, $data, $journalEntry, $lines): JournalEntry {
            $journalEntry->load('lines');
            $oldValues = $journalEntry->attributesToArray();
            $oldValues['lines'] = $journalEntry->lines->map->attributesToArray()->all();

            $journalEntry->update($data);

            if (is_array($lines)) {
                $journalEntry->lines()->delete();
                $this->createLines($journalEntry, $lines);
            }

            $this->auditLogger->log(
                action: 'journal_entry.updated',
                auditable: $journalEntry,
                oldValues: $oldValues,
                newValues: $journalEntry->refresh()->load('lines')->attributesToArray(),
                user: $actor,
                company: $journalEntry->company_id,
            );

            return $journalEntry;
        });
    }

    /**
     * @throws ValidationException
     */
    private function ensureDraft(JournalEntry $journalEntry): void
    {
        if ($journalEntry->status !== JournalEntryStatus::Draft) {
            throw ValidationException::withMessages([
                'status' => __('accounting.validation.journal_entries.editable_status'),
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    private function createLines(JournalEntry $journalEntry, array $lines): void
    {
        foreach ($lines as $index => $line) {
            $journalEntry->lines()->create([
                ...$line,
                'company_id' => $journalEntry->company_id,
                'line_order' => $line['line_order'] ?? $index + 1,
            ]);
        }
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to update journal entries.');
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
