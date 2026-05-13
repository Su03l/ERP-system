<?php

namespace App\Actions;

use App\Models\AssetCategory;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateAssetCategory
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $actor = null): AssetCategory
    {
        $actor = $this->actor($actor);
        $companyId = $this->companyId($actor);
        $this->authorize($actor, 'asset_categories.create', $companyId);
        unset($data['company_id']);

        return DB::transaction(function () use ($actor, $companyId, $data): AssetCategory {
            $assetCategory = AssetCategory::create([
                ...$data,
                'company_id' => $companyId,
            ]);

            $this->auditLogger->log('asset_category.created', $assetCategory, newValues: $assetCategory->attributesToArray(), user: $actor, company: $companyId);

            return $assetCategory;
        });
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to create asset categories.');
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

    private function authorize(User $actor, string $permission, int $companyId): void
    {
        if (! $actor->hasPermission($permission, $companyId)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }
}
