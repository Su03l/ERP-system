<?php

namespace App\Actions;

use App\Models\Asset;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateAsset
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Asset $asset, array $data, ?User $actor = null): Asset
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($asset, $actor);
        $this->authorize($actor, 'assets.update', $asset->company_id);
        unset($data['company_id']);

        return DB::transaction(function () use ($asset, $actor, $data): Asset {
            $oldValues = $asset->attributesToArray();

            $asset->update($data);

            $this->auditLogger->log('asset.updated', $asset, $oldValues, $asset->refresh()->attributesToArray(), user: $actor, company: $asset->company_id);

            return $asset;
        });
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to update assets.');
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
