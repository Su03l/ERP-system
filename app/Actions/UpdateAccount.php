<?php

namespace App\Actions;

use App\Models\Account;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class UpdateAccount
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Account $account, array $data, ?User $actor = null): Account
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($account, $actor);
        Gate::forUser($actor)->authorize('update', $account);
        unset($data['company_id']);
        $this->ensureValidParent($account, $data['parent_id'] ?? null);

        return DB::transaction(function () use ($account, $actor, $data): Account {
            $oldValues = $account->attributesToArray();

            if (array_key_exists('parent_id', $data) && ! array_key_exists('level', $data)) {
                $data['level'] = $this->resolveLevel($data['parent_id']);
            }

            $account->update($data);

            $this->auditLogger->log('account.updated', $account, $oldValues, $account->refresh()->attributesToArray(), user: $actor, company: $account->company_id);

            return $account;
        });
    }

    /**
     * @throws ValidationException
     */
    private function ensureValidParent(Account $account, mixed $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        if ((int) $parentId === $account->id) {
            throw ValidationException::withMessages([
                'parent_id' => __('accounting.validation.accounts.parent_self'),
            ]);
        }
    }

    private function resolveLevel(mixed $parentId): int
    {
        if ($parentId === null) {
            return 1;
        }

        $parent = Account::query()->find($parentId);

        return $parent === null ? 1 : $parent->level + 1;
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to update accounts.');
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
