<?php

namespace App\Actions;

use App\Models\CompanyApiToken;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RevokeApiToken
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(CompanyApiToken $token, ?User $actor = null): CompanyApiToken
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to revoke API tokens.');
        }

        Gate::forUser($actor)->authorize('revoke', $token);

        return DB::transaction(function () use ($actor, $token): CompanyApiToken {
            $oldValues = ['revoked_at' => $token->revoked_at?->toDateTimeString()];

            $token->forceFill(['revoked_at' => now()])->save();

            $this->auditLogger->log(
                'api_token.revoked',
                $token,
                oldValues: $oldValues,
                newValues: ['revoked_at' => $token->revoked_at?->toDateTimeString()],
                user: $actor,
                company: $token->company_id,
            );

            return $token->refresh();
        });
    }
}
