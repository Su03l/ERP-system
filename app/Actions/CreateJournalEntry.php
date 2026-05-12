<?php

namespace App\Actions;

use App\Enums\JournalEntrySource;
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

class CreateJournalEntry
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly JournalEntryLineValidator $lineValidator,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $actor = null): JournalEntry
    {
        $actor = $this->actor($actor);
        Gate::forUser($actor)->authorize('journal_entries.create');
        $companyId = $this->companyId($actor);
        $lines = $data['lines'] ?? [];
        unset($data['lines'], $data['company_id'], $data['status'], $data['posted_by'], $data['posted_at'], $data['approved_by'], $data['approved_at']);

        $this->lineValidator->validateLines($lines, $companyId);

        return DB::transaction(function () use ($actor, $companyId, $data, $lines): JournalEntry {
            $journalEntry = JournalEntry::create([
                ...$data,
                'company_id' => $companyId,
                'source' => $data['source'] ?? JournalEntrySource::Manual,
                'status' => JournalEntryStatus::Draft,
            ]);

            $this->createLines($journalEntry, $lines);

            $this->auditLogger->log(
                action: 'journal_entry.created',
                auditable: $journalEntry,
                newValues: $journalEntry->load('lines')->attributesToArray(),
                user: $actor,
                company: $companyId,
            );

            return $journalEntry->refresh()->load('lines');
        });
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
            throw new AuthorizationException('An authenticated user is required to create journal entries.');
        }

        return $actor;
    }

    private function companyId(User $actor): int
    {
        $companyId = $this->tenantContext->companyId();

        if ($companyId === null || $actor->company_id !== $companyId) {
            throw new AuthorizationException('A current company is required.');
        }

        return $companyId;
    }
}
