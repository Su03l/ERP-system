<?php

namespace App\Actions;

use App\Enums\JournalEntryStatus;
use App\Models\Account;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ArchiveAccount
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(Account $account, ?User $actor = null): Account
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($account, $actor);
        Gate::forUser($actor)->authorize('delete', $account);
        $this->ensureNoPostedJournalLines($account);

        return DB::transaction(function () use ($account, $actor): Account {
            $oldValues = $account->attributesToArray();

            $account->forceFill(['is_active' => false])->save();

            $this->auditLogger->log('account.archived', $account, $oldValues, $account->refresh()->attributesToArray(), user: $actor, company: $account->company_id);

            return $account;
        });
    }

    /**
     * @throws ValidationException
     */
    private function ensureNoPostedJournalLines(Account $account): void
    {
        $hasPostedLines = $account->journalEntryLines()
            ->whereHas('journalEntry', fn ($query) => $query->where('status', JournalEntryStatus::Posted))
            ->exists();

        if ($hasPostedLines) {
            throw ValidationException::withMessages([
                'account' => __('accounting.validation.accounts.posted_lines'),
            ]);
        }
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to archive accounts.');
        }

        return $actor;
    }

    private function ensureTenant(Account $account, User $actor): void
    {
        if ($this->tenantContext->companyId() !== $account->company_id || $actor->company_id !== $account->company_id) {
            throw new AuthorizationException('Account does not belong to the current company.');
        }
    }
}
