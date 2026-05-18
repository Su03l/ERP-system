<?php

namespace App\Services;

use App\Models\CompanyApiToken;
use App\Models\User;
use App\Models\UserSession;
use App\Models\WebhookDelivery;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;

class SecurityExportService
{
    public function __construct(
        private readonly AuditLogExportQuery $auditLogs,
        private readonly AuditLogger $auditLogger,
        private readonly SensitiveExportApprovalGuard $approvalGuard,
        private readonly SecurityNotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array{entity_type: string, module_key: string, columns: array<int, array{key: string, label: string}>, rows: array<int, array<string, mixed>>}
     */
    public function auditLogs(User $actor, array $filters = []): array
    {
        $this->authorize($actor, 'audit_logs.export');
        $this->authorizeSensitiveExport($actor, 'audit_logs');

        return $this->auditLogs->export($filters, $actor);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{entity_type: string, module_key: string, columns: array<int, array{key: string, label: string}>, rows: array<int, array<string, mixed>>}
     */
    public function apiTokens(User $actor, array $filters = []): array
    {
        $this->authorize($actor, 'api_tokens.view');

        return [
            'entity_type' => 'api_tokens',
            'module_key' => 'security',
            'columns' => $this->columns(['name', 'abilities', 'last_used_at', 'expires_at', 'revoked_at']),
            'rows' => CompanyApiToken::query()
                ->forCompany($actor->company_id)
                ->when($filters['revoked'] ?? null, fn (Builder $query): Builder => $query->whereNotNull('revoked_at'))
                ->latest('id')
                ->get()
                ->map(fn (CompanyApiToken $token): array => [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at?->toDateTimeString(),
                    'expires_at' => $token->expires_at?->toDateTimeString(),
                    'revoked_at' => $token->revoked_at?->toDateTimeString(),
                ])
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{entity_type: string, module_key: string, columns: array<int, array{key: string, label: string}>, rows: array<int, array<string, mixed>>}
     */
    public function webhookDeliveries(User $actor, array $filters = []): array
    {
        $this->authorize($actor, 'webhooks.view');

        return [
            'entity_type' => 'webhook_deliveries',
            'module_key' => 'security',
            'columns' => $this->columns(['event_name', 'status', 'response_status', 'attempt_count', 'delivered_at', 'failed_at']),
            'rows' => WebhookDelivery::query()
                ->forCompany($actor->company_id)
                ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
                ->latest('id')
                ->get()
                ->map(fn (WebhookDelivery $delivery): array => [
                    'id' => $delivery->id,
                    'event_name' => $delivery->event_name,
                    'status' => $delivery->status,
                    'response_status' => $delivery->response_status,
                    'attempt_count' => $delivery->attempt_count,
                    'delivered_at' => $delivery->delivered_at?->toDateTimeString(),
                    'failed_at' => $delivery->failed_at?->toDateTimeString(),
                    'error_message' => $delivery->error_message,
                ])
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{entity_type: string, module_key: string, columns: array<int, array{key: string, label: string}>, rows: array<int, array<string, mixed>>}
     */
    public function userSessions(User $actor, array $filters = []): array
    {
        $this->authorize($actor, 'user_sessions.view');

        return [
            'entity_type' => 'user_sessions',
            'module_key' => 'security',
            'columns' => $this->columns(['user_id', 'ip_address', 'last_activity_at', 'revoked_at']),
            'rows' => UserSession::query()
                ->where('company_id', $actor->company_id)
                ->when($filters['revoked'] ?? null, fn (Builder $query): Builder => $query->whereNotNull('revoked_at'))
                ->latest('last_activity_at')
                ->get()
                ->map(fn (UserSession $session): array => [
                    'id' => $session->id,
                    'user_id' => $session->user_id,
                    'ip_address' => $session->ip_address,
                    'last_activity_at' => $session->last_activity_at?->toDateTimeString(),
                    'revoked_at' => $session->revoked_at?->toDateTimeString(),
                ])
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{entity_type: string, module_key: string, columns: array<int, array{key: string, label: string}>, rows: array<int, array<string, mixed>>}
     */
    public function securityEvents(User $actor, array $filters = []): array
    {
        $this->authorize($actor, 'security_settings.view');

        return [
            'entity_type' => 'security_events',
            'module_key' => 'security',
            'columns' => $this->columns(['event', 'company_id', 'created_at']),
            'rows' => DatabaseNotification::query()
                ->where('notifiable_type', $actor->getMorphClass())
                ->where('notifiable_id', $actor->id)
                ->latest('created_at')
                ->get()
                ->filter(fn (DatabaseNotification $notification): bool => ($notification->data['type'] ?? null) === 'security_event')
                ->filter(fn (DatabaseNotification $notification): bool => ! isset($filters['event']) || ($notification->data['event'] ?? null) === $filters['event'])
                ->map(fn (DatabaseNotification $notification): array => [
                    'id' => $notification->id,
                    'event' => $notification->data['event'] ?? null,
                    'company_id' => $notification->data['company_id'] ?? null,
                    'created_at' => $notification->created_at?->toDateTimeString(),
                ])
                ->values()
                ->all(),
        ];
    }

    private function authorize(User $actor, string $permission): void
    {
        if ($actor->company_id === null || ! $actor->hasPermission($permission, $actor->company_id)) {
            throw new AuthorizationException("Missing permission [{$permission}].");
        }
    }

    private function authorizeSensitiveExport(User $actor, string $exportKey): void
    {
        $approvalRequired = false;

        DB::transaction(function () use ($actor, $exportKey, &$approvalRequired): void {
            $this->auditLogger->log(
                'sensitive_export.requested',
                null,
                newValues: ['export_key' => $exportKey],
                user: $actor,
                company: $actor->company_id,
            );

            if (! $this->approvalGuard->canExportDirectly($actor, $exportKey, $actor->company_id)) {
                $approvalRequired = true;
                $this->notifications->suspiciousExportRequested($actor, $exportKey, $actor->company_id);

                $this->auditLogger->log(
                    'sensitive_export.approval_required',
                    null,
                    newValues: ['export_key' => $exportKey],
                    user: $actor,
                    company: $actor->company_id,
                );
            }
        });

        if ($approvalRequired) {
            throw new AuthorizationException('This export requires approval.');
        }
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<int, array{key: string, label: string}>
     */
    private function columns(array $keys): array
    {
        return collect($keys)
            ->map(fn (string $key): array => ['key' => $key, 'label' => __("security.exports.columns.{$key}")])
            ->all();
    }
}
