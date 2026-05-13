<?php

namespace App\Actions;

use App\Enums\AssetStatus;
use App\Enums\CustodyStatus;
use App\Models\Asset;
use App\Models\AssetCustody;
use App\Models\User;
use App\Models\Workflow;
use App\Services\AuditLogger;
use App\Services\WorkflowExecutionService;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ReturnAssetFromEmployee
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
        private readonly WorkflowExecutionService $workflowExecutionService,
    ) {}

    public function handle(Asset $asset, ?User $actor = null, ?string $notesAr = null, ?string $notesEn = null): AssetCustody
    {
        $actor = $this->actor($actor);
        Gate::forUser($actor)->authorize('asset_custody.return');
        $this->ensureTenant($asset, $actor);
        $this->ensureAssigned($asset);

        return DB::transaction(function () use ($asset, $actor, $notesAr, $notesEn): AssetCustody {
            $asset->loadMissing('company.assetSetting');
            $custody = AssetCustody::query()
                ->where('company_id', $asset->company_id)
                ->where('asset_id', $asset->id)
                ->where('status', CustodyStatus::Assigned)
                ->latest('id')
                ->firstOrFail();
            $oldValues = $custody->attributesToArray();
            $approvalRequired = $asset->company->assetSetting?->asset_return_approval_required ?? true;
            $workflow = $approvalRequired ? $this->workflow($asset->company_id, 'asset_return_approval') : null;

            $custody->forceFill([
                'status' => $workflow instanceof Workflow ? CustodyStatus::Pending : CustodyStatus::Returned,
                'returned_at' => $workflow instanceof Workflow ? null : now(),
                'return_received_by' => $workflow instanceof Workflow ? null : $actor->id,
                'notes_ar' => $notesAr ?? $custody->notes_ar,
                'notes_en' => $notesEn ?? $custody->notes_en,
            ])->save();

            if ($workflow instanceof Workflow) {
                $instance = $this->workflowExecutionService->start($workflow, $actor, $custody, [
                    'asset_id' => $asset->id,
                    'employee_id' => $asset->assigned_employee_id,
                ]);

                $custody->forceFill(['workflow_instance_id' => $instance->id])->save();
            } else {
                $asset->forceFill([
                    'assigned_employee_id' => null,
                    'status' => AssetStatus::Available,
                ])->save();
            }

            $this->auditLogger->log('asset_custody.returned', $custody, $oldValues, $custody->refresh()->attributesToArray(), user: $actor, company: $asset->company_id);

            return $custody;
        });
    }

    private function ensureAssigned(Asset $asset): void
    {
        if ($asset->status !== AssetStatus::Assigned || $asset->assigned_employee_id === null) {
            throw ValidationException::withMessages([
                'asset' => __('assets.validation.asset_custodies.asset_not_assigned'),
            ]);
        }
    }

    private function ensureTenant(Asset $asset, User $actor): void
    {
        if ($this->tenantContext->companyId() !== $asset->company_id || $actor->company_id !== $asset->company_id) {
            throw new AuthorizationException('Asset does not belong to the current company.');
        }
    }

    private function workflow(int $companyId, string $triggerType): ?Workflow
    {
        return Workflow::query()
            ->where('company_id', $companyId)
            ->where('module_key', 'assets')
            ->where('trigger_type', $triggerType)
            ->where('status', 'active')
            ->with('steps')
            ->latest('id')
            ->first();
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to return assets.');
        }

        return $actor;
    }
}
