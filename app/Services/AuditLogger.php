<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public function __construct(
        private readonly Request $request,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     * @param  array<string, mixed>|null  $metadata
     */
    public function log(
        string $action,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null,
        User|int|null $user = null,
        Company|int|null $company = null,
    ): AuditLog {
        return AuditLog::create([
            'company_id' => $this->resolveCompanyId($company),
            'user_id' => $this->resolveUserId($user),
            'action' => $action,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'metadata' => $metadata,
        ]);
    }

    private function resolveCompanyId(Company|int|null $company): ?int
    {
        if ($company instanceof Company) {
            return $company->id;
        }

        return $company ?? $this->tenantContext->companyId();
    }

    private function resolveUserId(User|int|null $user): ?int
    {
        if ($user instanceof User) {
            return $user->id;
        }

        return $user ?? Auth::id();
    }
}
