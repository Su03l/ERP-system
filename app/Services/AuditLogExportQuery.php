<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AuditLogExportQuery
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function rows(array $filters = [], ?User $actor = null, string $permission = 'audit_logs.view'): array
    {
        [$actor, $companyId] = $this->authorize($actor, $permission);

        return $this->query($filters, $companyId)
            ->with('user')
            ->latest('id')
            ->get()
            ->map(fn (AuditLog $auditLog): array => $this->row($auditLog))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{entity_type: string, module_key: string, columns: array<int, array{key: string, label: string}>, rows: array<int, array<string, mixed>>}
     */
    public function export(array $filters = [], ?User $actor = null): array
    {
        return [
            'entity_type' => 'audit_logs',
            'module_key' => 'security',
            'columns' => $this->columns(),
            'rows' => $this->rows($filters, $actor, 'audit_logs.export'),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<AuditLog>
     */
    private function query(array $filters, int $companyId): Builder
    {
        return AuditLog::query()
            ->where('company_id', $companyId)
            ->when($filters['user_id'] ?? null, fn (Builder $query, int|string $userId): Builder => $query->where('user_id', $userId))
            ->when($filters['action'] ?? null, fn (Builder $query, string $action): Builder => $query->where('action', 'like', "%{$action}%"))
            ->when($filters['auditable_type'] ?? null, fn (Builder $query, string $type): Builder => $query->where('auditable_type', $type))
            ->when($filters['auditable_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->where('auditable_id', $id))
            ->when($filters['ip_address'] ?? null, fn (Builder $query, string $ip): Builder => $query->where('ip_address', $ip))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date));
    }

    /**
     * @return array<string, mixed>
     */
    private function row(AuditLog $auditLog): array
    {
        return [
            'id' => $auditLog->id,
            'created_at' => $auditLog->created_at?->toDateTimeString(),
            'company_id' => $auditLog->company_id,
            'user_id' => $auditLog->user_id,
            'user_name' => $auditLog->user?->name,
            'action' => $auditLog->action,
            'action_label' => $this->label($auditLog->action),
            'auditable_type' => $auditLog->auditable_type,
            'auditable_id' => $auditLog->auditable_id,
            'ip_address' => $auditLog->ip_address,
            'metadata' => $auditLog->metadata,
        ];
    }

    /**
     * @return array{0: User, 1: int}
     */
    private function authorize(?User $actor, string $permission): array
    {
        $actor ??= Auth::user();
        $companyId = $this->tenantContext->companyId();

        if (! $actor instanceof User || $companyId === null || $actor->company_id !== $companyId || ! $actor->hasPermission($permission, $companyId)) {
            throw new AuthorizationException('You are not authorized to access audit logs.');
        }

        return [$actor, $companyId];
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    private function columns(): array
    {
        return [
            ['key' => 'created_at', 'label' => __('security.audit.columns.created_at')],
            ['key' => 'user_name', 'label' => __('security.audit.columns.user')],
            ['key' => 'action_label', 'label' => __('security.audit.columns.action')],
            ['key' => 'auditable_type', 'label' => __('security.audit.columns.entity')],
            ['key' => 'auditable_id', 'label' => __('security.audit.columns.entity_id')],
            ['key' => 'ip_address', 'label' => __('security.audit.columns.ip_address')],
        ];
    }

    private function label(string $action): string
    {
        return __('security.audit.actions.'.str_replace('.', '_', $action));
    }
}
