<?php

namespace App\Actions;

use App\Enums\AssetStatus;
use App\Enums\CustodyStatus;
use App\Models\Asset;
use App\Models\AssetCustody;
use App\Models\Employee;
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

class AssignAssetToEmployee
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
        private readonly WorkflowExecutionService $workflowExecutionService,
    ) {}

    public function handle(Asset $asset, Employee $employee, ?User $actor = null, ?string $notesAr = null, ?string $notesEn = null): AssetCustody
    {
        $actor = $this->actor($actor);
        Gate::forUser($actor)->authorize('asset_custody.create');
        $this->ensureTenant($asset, $employee, $actor);
        $this->ensureAvailable($asset);

        return DB::transaction(function () use ($asset, $employee, $actor, $notesAr, $notesEn): AssetCustody {
            $asset->loadMissing('company.assetSetting');
            $approvalRequired = $asset->company->assetSetting?->custody_approval_required ?? true;
            $workflow = $approvalRequired ? $this->workflow($asset->company_id, 'asset_custody_approval') : null;

            $custody = AssetCustody::create([
                'company_id' => $asset->company_id,
                'asset_id' => $asset->id,
                'employee_id' => $employee->id,
                'assigned_by' => $actor->id,
                'assigned_at' => $workflow instanceof Workflow ? null : now(),
                'status' => $workflow instanceof Workflow ? CustodyStatus::Pending : CustodyStatus::Assigned,
                'notes_ar' => $notesAr,
                'notes_en' => $notesEn,
                'metadata' => [],
            ]);

            if ($workflow instanceof Workflow) {
                $instance = $this->workflowExecutionService->start($workflow, $actor, $custody, [
                    'asset_id' => $asset->id,
                    'employee_id' => $employee->id,
                ]);

                $custody->forceFill(['workflow_instance_id' => $instance->id])->save();
            } else {
                $asset->forceFill([
                    'assigned_employee_id' => $employee->id,
                    'status' => AssetStatus::Assigned,
                ])->save();
            }

            $this->auditLogger->log('asset_custody.assigned', $custody, newValues: $custody->refresh()->attributesToArray(), user: $actor, company: $asset->company_id);

            return $custody;
        });
    }

    private function ensureAvailable(Asset $asset): void
    {
        if ($asset->status !== AssetStatus::Available || $asset->assigned_employee_id !== null) {
            throw ValidationException::withMessages([
                'asset' => __('assets.validation.asset_custodies.asset_unavailable'),
            ]);
        }
    }

    private function ensureTenant(Asset $asset, Employee $employee, User $actor): void
    {
        if ($this->tenantContext->companyId() !== $asset->company_id || $actor->company_id !== $asset->company_id || $employee->company_id !== $asset->company_id) {
            throw new AuthorizationException('Asset custody records must stay inside the current company.');
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
            throw new AuthorizationException('An authenticated user is required to assign assets.');
        }

        return $actor;
    }
}
