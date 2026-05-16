<?php

namespace App\Actions;

use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Services\AuditLogger;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeleteWebhookEndpoint
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(WebhookEndpoint $endpoint, ?User $actor = null): void
    {
        $actor ??= Auth::user();

        abort_unless($actor instanceof User && $actor->company_id === $endpoint->company_id && $actor->hasPermission('webhooks.delete', $endpoint->company_id), 404);

        DB::transaction(function () use ($endpoint, $actor): void {
            $oldValues = Arr::except($endpoint->attributesToArray(), ['secret_hash']);
            $endpoint->delete();
            $this->auditLogger->log('webhook_endpoint.deleted', null, oldValues: $oldValues, user: $actor, company: $oldValues['company_id'] ?? null);
        });
    }
}
