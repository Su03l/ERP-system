<?php

namespace App\Actions;

use App\Models\JobTitle;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateJobTitle
{
    public function __construct(private readonly AuditLogger $auditLogger, private readonly TenantContext $tenantContext) {}

    /** @param array<string, mixed> $data */
    public function handle(JobTitle $jobTitle, array $data, ?User $actor = null): JobTitle
    {
        $actor ??= Auth::user();
        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to update job titles.');
        }
        Gate::forUser($actor)->authorize('update', $jobTitle);
        if ($this->tenantContext->companyId() !== $jobTitle->company_id) {
            throw new AuthorizationException('Job title does not belong to the current company.');
        }

        return DB::transaction(function () use ($actor, $data, $jobTitle): JobTitle {
            $oldValues = $jobTitle->attributesToArray();
            $jobTitle->fill($data);
            $jobTitle->save();
            $this->auditLogger->log('job_title.updated', $jobTitle, $oldValues, $jobTitle->refresh()->attributesToArray(), user: $actor, company: $jobTitle->company_id);

            return $jobTitle;
        });
    }
}
