<?php

namespace App\Actions;

use App\Models\SecuritySetting;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class UpdateSecuritySetting
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(SecuritySetting $setting, array $data, User $actor): SecuritySetting
    {
        if ($actor->company_id === null || $actor->company_id !== $setting->company_id || ! $actor->hasPermission('security_settings.update', $setting->company_id)) {
            throw new AuthorizationException('You are not authorized to update security settings.');
        }

        return DB::transaction(function () use ($setting, $data, $actor): SecuritySetting {
            $oldValues = $setting->only(array_keys($data));

            $setting->fill($data)->save();

            $this->auditLogger->log(
                'security_settings.updated',
                $setting,
                oldValues: $oldValues,
                newValues: $setting->only(array_keys($data)),
                user: $actor,
                company: $setting->company_id,
            );

            return $setting->refresh();
        });
    }
}
