<?php

namespace App\Actions;

use App\Enums\AssetStatus;
use App\Enums\CustodyStatus;
use App\Models\AssetCustody;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\WorkflowExecutionService;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ApproveAssetReturn
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
        private readonly WorkflowExecutionService $workflowExecutionService,
    ) {}

    public function handle(AssetCustody $custody, ?User $actor = null, ?string $comment = null): AssetCustody
    {
        $actor = $this->actor($actor);
        Gate::forUser($actor)->authorize('asset_custody.approve');
        $this->ensureTenant($custody, $actor);

        return DB::transaction(function () use ($custody, $actor, $comment): AssetCustody {
            $custody->loadMissing('asset', 'workflowInstance');
            $oldValues = $custody->attributesToArray();

            if ($custody->workflowInstance !== null && $custody->workflowInstance->status === 'pending') {
                $this->workflowExecutionService->approve($custody->workflowInstance, $actor, $comment);
                $custody->workflowInstance->refresh();
            }

            if ($custody->workflowInstance === null || $custody->workflowInstance->status === 'completed') {
                $custody->forceFill([
                    'status' => CustodyStatus::Returned,
                    'returned_at' => $custody->returned_at ?? now(),
                    'return_received_by' => $actor->id,
                ])->save();

                $custody->asset->forceFill([
                    'assigned_employee_id' => null,
                    'status' => AssetStatus::Available,
                ])->save();
            }

            $this->auditLogger->log('asset_custody.return_approved', $custody, $oldValues, $custody->refresh()->attributesToArray(), metadata: ['comment' => $comment], user: $actor, company: $custody->company_id);

            return $custody;
        });
    }

    private function ensureTenant(AssetCustody $custody, User $actor): void
    {
        if ($this->tenantContext->companyId() !== $custody->company_id || $actor->company_id !== $custody->company_id) {
            throw new AuthorizationException('Asset custody does not belong to the current company.');
        }
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to approve asset returns.');
        }

        return $actor;
    }
}
