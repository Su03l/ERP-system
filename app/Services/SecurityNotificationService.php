<?php

namespace App\Services;

use App\Models\CompanyApiToken;
use App\Models\SecuritySetting;
use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Notifications\SecurityEventNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;

class SecurityNotificationService
{
    public function apiTokenCreated(User $actor, CompanyApiToken $token): void
    {
        $this->notify($actor, 'api_token.created', [
            'company_id' => $token->company_id,
            'api_token_id' => $token->id,
            'api_token_name' => $token->name,
        ]);
    }

    public function apiTokenRevoked(User $actor, CompanyApiToken $token): void
    {
        $this->notify($actor, 'api_token.revoked', [
            'company_id' => $token->company_id,
            'api_token_id' => $token->id,
            'api_token_name' => $token->name,
        ]);
    }

    public function webhookFailureThresholdReached(WebhookEndpoint $endpoint, int $threshold = 3): void
    {
        if ($endpoint->failure_count < $threshold || $endpoint->failure_count % $threshold !== 0) {
            return;
        }

        $this->companyRecipients($endpoint->company_id, 'webhooks.view')
            ->each(fn (User $user): mixed => $this->notify($user, 'webhook.failure_threshold_reached', [
                'company_id' => $endpoint->company_id,
                'webhook_endpoint_id' => $endpoint->id,
                'webhook_endpoint_name' => $endpoint->name,
                'failure_count' => $endpoint->failure_count,
            ]));
    }

    public function suspiciousExportRequested(User $actor, string $exportKey, int|string|null $companyId = null): void
    {
        $this->notify($actor, 'export.suspicious_requested', [
            'company_id' => $companyId ?? $actor->company_id,
            'export_key' => $exportKey,
        ]);
    }

    public function subscriptionAccessBlocked(User $actor, string $reason, int|string|null $companyId = null): void
    {
        $this->notify($actor, 'subscription.access_blocked', [
            'company_id' => $companyId ?? $actor->company_id,
            'reason' => $reason,
        ]);
    }

    public function securitySettingsChanged(User $actor, SecuritySetting $setting): void
    {
        $this->notify($actor, 'security_settings.changed', [
            'company_id' => $setting->company_id,
            'security_setting_id' => $setting->id,
        ]);
    }

    /**
     * @return Collection<int, User>
     */
    private function companyRecipients(int $companyId, string $permission): Collection
    {
        return User::query()
            ->where('company_id', $companyId)
            ->get()
            ->filter(fn (User $user): bool => $user->hasPermission($permission, $companyId))
            ->values();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function notify(User $user, string $event, array $data): void
    {
        if ($this->recentDuplicateExists($user, $event, $data)) {
            return;
        }

        $user->notify(new SecurityEventNotification($event, $data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function recentDuplicateExists(User $user, string $event, array $data): bool
    {
        return DatabaseNotification::query()
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->id)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->get()
            ->contains(fn (DatabaseNotification $notification): bool => ($notification->data['event'] ?? null) === $event
                && ($notification->data['company_id'] ?? null) === ($data['company_id'] ?? null));
    }
}
