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

class ArchiveJobTitle
{
    public function __construct(private readonly AuditLogger $auditLogger, private readonly TenantContext $tenantContext) {}

    public function handle(JobTitle $jobTitle, ?User $actor = null): JobTitle
    {
        $actor ??= Auth::user();
        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to archive job titles.');
        }
        Gate::forUser($actor)->authorize('delete', $jobTitle);
        if ($this->tenantContext->companyId() !== $jobTitle->company_id) {
            throw new AuthorizationException('Job title does not belong to the current company.');
        }

        return DB::transaction(function () use ($actor, $jobTitle): JobTitle {
            $oldValues = $jobTitle->attributesToArray();
            $jobTitle->delete();
            $this->auditLogger->log('job_title.archived', $jobTitle, $oldValues, $jobTitle->refresh()->attributesToArray(), user: $actor, company: $jobTitle->company_id);

            return $jobTitle;
        });
    }
}
