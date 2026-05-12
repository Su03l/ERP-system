<?php

namespace App\Services;

use App\Jobs\GeneratePayrollRunJob;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;

class PayrollGenerationQueueService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function dispatch(PayrollPeriod $period, array $data = [], ?User $actor = null): string
    {
        $actor ??= Auth::user();
        $companyId = $this->tenantContext->companyId();

        if (! $actor instanceof User || $companyId === null || $period->company_id !== $companyId || ! $actor->hasPermission('payroll_runs.generate', $companyId)) {
            throw new AuthorizationException('You are not authorized to queue payroll generation.');
        }

        $jobKey = "payroll-generation:{$companyId}:{$period->id}";
        $this->updateStatus($period, 'queued', $jobKey);

        Bus::dispatch((new GeneratePayrollRunJob($period->id, $actor->id, $companyId, $data))->afterCommit());

        $this->auditLogger->log(
            action: 'payroll_generation.queued',
            auditable: $period,
            newValues: ['status' => 'queued', 'job_key' => $jobKey],
            user: $actor,
            company: $companyId,
        );

        return $jobKey;
    }

    public function updateStatus(PayrollPeriod $period, string $status, ?string $jobKey = null, ?string $error = null): void
    {
        $metadata = $period->metadata ?? [];
        $metadata['payroll_generation'] = array_filter([
            'status' => $status,
            'job_key' => $jobKey ?? ($metadata['payroll_generation']['job_key'] ?? null),
            'error' => $error,
            'updated_at' => now()->toJSON(),
        ], fn (mixed $value): bool => $value !== null);

        $period->forceFill(['metadata' => $metadata])->save();
    }
}
