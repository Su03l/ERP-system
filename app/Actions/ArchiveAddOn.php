<?php

namespace App\Actions;

use App\Enums\AddOnStatus;
use App\Models\AddOn;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class ArchiveAddOn
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(AddOn $addOn, ?User $actor = null): AddOn
    {
        return DB::transaction(function () use ($actor, $addOn): AddOn {
            $oldValues = $addOn->attributesToArray();
            $addOn->forceFill(['status' => AddOnStatus::Archived])->save();

            $this->auditLogger->log(
                action: 'add_on.archived',
                auditable: $addOn,
                oldValues: $oldValues,
                newValues: $addOn->refresh()->attributesToArray(),
                user: $actor,
            );

            return $addOn;
        });
    }
}
