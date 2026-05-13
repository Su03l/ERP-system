<?php

namespace App\Actions;

use App\Models\AssetCategory;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateAssetCategory
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(AssetCategory $assetCategory, array $data, ?User $actor = null): AssetCategory
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($assetCategory, $actor);
        $this->authorize($actor, 'asset_categories.update', $assetCategory->company_id);
        unset($data['company_id']);
        $this->ensureValidParent($assetCategory, $data['parent_id'] ?? null);

        return DB::transaction(function () use ($assetCategory, $actor, $data): AssetCategory {
            $oldValues = $assetCategory->attributesToArray();

            $assetCategory->update($data);

            $this->auditLogger->log('asset_category.updated', $assetCategory, $oldValues, $assetCategory->refresh()->attributesToArray(), user: $actor, company: $assetCategory->company_id);

            return $assetCategory;
        });
    }

    private function ensureValidParent(AssetCategory $assetCategory, mixed $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        if ((int) $parentId === $assetCategory->id) {
            throw ValidationException::withMessages([
                'parent_id' => __('assets.validation.asset_categories.parent_self'),
            ]);
        }
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to update asset categories.');
        }

        return $actor;
    }

    private function ensureTenant(AssetCategory $assetCategory, User $actor): void
    {
        if ($this->tenantContext->companyId() !== $assetCategory->company_id || $actor->company_id !== $assetCategory->company_id) {
            throw new AuthorizationException('Asset category does not belong to the current company.');
        }
    }

    private function authorize(User $actor, string $permission, int $companyId): void
    {
        if (! $actor->hasPermission($permission, $companyId)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }
}
