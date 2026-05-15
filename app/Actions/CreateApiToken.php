<?php

namespace App\Actions;

use App\Models\CompanyApiToken;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class CreateApiToken
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{token: CompanyApiToken, plain_text_token: string}
     */
    public function handle(array $data, ?User $actor = null): array
    {
        $actor = $this->actor($actor);
        Gate::forUser($actor)->authorize('create', CompanyApiToken::class);
        $companyId = $this->companyId($actor);
        $plainToken = Str::random(64);

        return DB::transaction(function () use ($actor, $companyId, $data, $plainToken): array {
            $token = CompanyApiToken::query()->create([
                'company_id' => $companyId,
                'user_id' => $actor->id,
                'name' => $data['name'],
                'token' => hash('sha256', $plainToken),
                'abilities' => $data['abilities'] ?? [],
                'expires_at' => $data['expires_at'] ?? null,
                'metadata' => $data['metadata'] ?? null,
            ]);

            $this->auditLogger->log(
                'api_token.created',
                $token,
                newValues: [
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'expires_at' => $token->expires_at?->toDateTimeString(),
                ],
                user: $actor,
                company: $companyId,
            );

            return [
                'token' => $token,
                'plain_text_token' => $plainToken,
            ];
        });
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to create API tokens.');
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
