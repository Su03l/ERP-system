<?php

namespace App\Actions;

use App\Models\AddOn;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class UpdateAddOn
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $data */
    public function handle(AddOn $addOn, array $data, ?User $actor = null): AddOn
    {
        return DB::transaction(function () use ($actor, $addOn, $data): AddOn {
            $oldValues = $addOn->attributesToArray();
            $addOn->update($data);

            $this->auditLogger->log(
                action: 'add_on.updated',
                auditable: $addOn,
                oldValues: $oldValues,
                newValues: $addOn->refresh()->attributesToArray(),
                user: $actor,
            );

            return $addOn;
        });
    }
}
