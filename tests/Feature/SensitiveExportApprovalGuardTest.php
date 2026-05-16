<?php

use App\Models\Company;
use App\Models\SecuritySetting;
use App\Models\User;
use App\Services\SensitiveExportApprovalGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('detects sensitive exports and approval requirements', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    SecuritySetting::factory()->for($company)->create(['export_approval_required' => true]);
    $guard = app(SensitiveExportApprovalGuard::class);

    expect($guard->isSensitive('payroll.runs'))->toBeTrue()
        ->and($guard->isSensitive('hr.employees'))->toBeFalse()
        ->and($guard->requiresApproval('payroll.runs', $company))->toBeTrue()
        ->and($guard->canExportDirectly($user, 'payroll.runs', $company))->toBeFalse()
        ->and($guard->approvalPayload($user, 'payroll.runs', $company)['trigger_type'])->toBe('sensitive_export');
});
