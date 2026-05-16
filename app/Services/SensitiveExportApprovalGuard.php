<?php

namespace App\Services;

use App\Models\Company;
use App\Models\SecuritySetting;
use App\Models\User;

class SensitiveExportApprovalGuard
{
    private const SENSITIVE_EXPORTS = [
        'payroll.runs',
        'salary_packages',
        'audit_logs',
        'accounting.financial',
        'employee_documents',
    ];

    public function isSensitive(string $exportKey): bool
    {
        return in_array($exportKey, self::SENSITIVE_EXPORTS, true);
    }

    public function requiresApproval(string $exportKey, Company|int $company): bool
    {
        if (! $this->isSensitive($exportKey)) {
            return false;
        }

        $companyId = $company instanceof Company ? $company->id : $company;
        $settings = SecuritySetting::query()->where('company_id', $companyId)->first();

        return $settings?->export_approval_required ?? false;
    }

    public function canExportDirectly(User $user, string $exportKey, Company|int $company): bool
    {
        $companyId = $company instanceof Company ? $company->id : $company;

        if ($user->company_id !== $companyId) {
            return false;
        }

        if (! $this->requiresApproval($exportKey, $companyId)) {
            return true;
        }

        return $user->hasPermission('exports.approve_sensitive', $companyId);
    }

    /** @return array<string, mixed> */
    public function approvalPayload(User $user, string $exportKey, Company|int $company): array
    {
        $companyId = $company instanceof Company ? $company->id : $company;

        return [
            'module_key' => 'security',
            'trigger_type' => 'sensitive_export',
            'company_id' => $companyId,
            'requested_by_id' => $user->id,
            'export_key' => $exportKey,
        ];
    }
}
