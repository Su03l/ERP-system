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

class CreateAccount
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $actor = null): Account
    {
        $actor = $this->actor($actor);
        Gate::forUser($actor)->authorize('create', Account::class);
        $companyId = $this->companyId($actor);
        unset($data['company_id']);

        return DB::transaction(function () use ($actor, $companyId, $data): Account {
            $account = Account::create([
                ...$data,
                'company_id' => $companyId,
                'level' => $data['level'] ?? $this->resolveLevel($data['parent_id'] ?? null),
                'is_active' => $data['is_active'] ?? true,
                'is_system' => $data['is_system'] ?? false,
            ]);

            $this->auditLogger->log('account.created', $account, newValues: $account->attributesToArray(), user: $actor, company: $companyId);

            return $account;
        });
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
            throw new AuthorizationException('An authenticated user is required to create accounts.');
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
