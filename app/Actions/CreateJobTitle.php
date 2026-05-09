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

class CreateJobTitle
{
    public function __construct(private readonly AuditLogger $auditLogger, private readonly TenantContext $tenantContext) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data, ?User $actor = null): JobTitle
    {
        $actor ??= Auth::user();
        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to create job titles.');
        }
        Gate::forUser($actor)->authorize('create', JobTitle::class);
        $companyId = $this->tenantContext->companyId();
        if ($companyId === null) {
            throw new AuthorizationException('A current company is required to create job titles.');
        }

        return DB::transaction(function () use ($actor, $companyId, $data): JobTitle {
            $jobTitle = JobTitle::create([...$data, 'company_id' => $companyId]);
            $this->auditLogger->log('job_title.created', $jobTitle, newValues: $jobTitle->attributesToArray(), user: $actor, company: $companyId);

            return $jobTitle;
        });
    }
}
