<?php

namespace App\Actions;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ArchiveAsset
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(Asset $asset, ?User $actor = null): Asset
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($asset, $actor);
        $this->authorize($actor, 'assets.delete', $asset->company_id);
        $this->ensureArchiveIsSafe($asset);

        return DB::transaction(function () use ($asset, $actor): Asset {
            $oldValues = $asset->attributesToArray();

            $asset->delete();

            $this->auditLogger->log('asset.archived', $asset, $oldValues, $asset->refresh()->attributesToArray(), user: $actor, company: $asset->company_id);

            return $asset;
        });
    }

    private function ensureArchiveIsSafe(Asset $asset): void
    {
        if ($asset->assigned_employee_id !== null || $asset->status === AssetStatus::Assigned) {
            throw ValidationException::withMessages([
                'asset' => __('assets.validation.assets.assigned_archive'),
            ]);
        }
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to archive assets.');
        }

        return $actor;
    }

    private function ensureTenant(Asset $asset, User $actor): void
    {
        if ($this->tenantContext->companyId() !== $asset->company_id || $actor->company_id !== $asset->company_id) {
            throw new AuthorizationException('Asset does not belong to the current company.');
        }
    }

    private function authorize(User $actor, string $permission, int $companyId): void
    {
        if (! $actor->hasPermission($permission, $companyId)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }
}
