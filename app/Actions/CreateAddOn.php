<?php

namespace App\Actions;

use App\Models\AddOn;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class CreateAddOn
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data, ?User $actor = null): AddOn
    {
        return DB::transaction(function () use ($actor, $data): AddOn {
            $addOn = AddOn::create($data);

            $this->auditLogger->log(
                action: 'add_on.created',
                auditable: $addOn,
                newValues: $addOn->attributesToArray(),
                user: $actor,
            );

            return $addOn->refresh();
        });
    }
}
