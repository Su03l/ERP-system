<?php

namespace App\Actions;

use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateWebhookEndpoint
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $actor = null): WebhookEndpoint
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to create webhooks.');
        }

        $companyId = $this->tenantContext->companyId();

        if ($companyId === null || $actor->company_id !== $companyId || ! $actor->hasPermission('webhooks.create', $companyId)) {
            throw new AuthorizationException('A current company is required.');
        }

        return DB::transaction(function () use ($data, $actor, $companyId): WebhookEndpoint {
            $endpoint = WebhookEndpoint::query()->create([
                ...Arr::except($data, ['secret']),
                'company_id' => $companyId,
                'secret_hash' => filled($data['secret'] ?? null) ? hash('sha256', $data['secret']) : null,
                'status' => $data['status'] ?? 'active',
            ]);

            $this->auditLogger->log('webhook_endpoint.created', $endpoint, newValues: Arr::except($endpoint->attributesToArray(), ['secret_hash']), user: $actor, company: $companyId);

            return $endpoint;
        });
    }
}
