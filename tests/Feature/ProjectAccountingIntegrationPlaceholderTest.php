<?php

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Services\ProjectAccountingIntegrationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantProjectAccountingIntegrationPermission(User $user): void
{
    $role = Role::factory()->for($user->company)->create();
    $permission = Permission::factory()->create(['key' => 'projects.update']);

    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('audits project accounting placeholder requests before rejecting unavailable integrations', function (string $method, array $arguments, string $action, string $integration, string $message) {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create();
    grantProjectAccountingIntegrationPermission($actor);

    $this->actingAs($actor);

    expect(fn () => app(ProjectAccountingIntegrationService::class)->{$method}($project, ...$arguments))
        ->toThrow(LogicException::class, $message);

    $auditLog = AuditLog::query()
        ->where('company_id', $company->id)
        ->where('user_id', $actor->id)
        ->where('action', $action)
        ->where('auditable_id', $project->id)
        ->first();

    expect($auditLog)->not->toBeNull()
        ->and($auditLog->metadata)->toMatchArray([
            'status' => 'accounting_module_not_ready',
            'integration' => $integration,
        ]);
})->with([
    'project invoice' => [
        'requestProjectInvoice',
        [],
        'project_accounting.project_invoice_requested',
        'project_invoicing',
        'Project invoicing accounting integration is not implemented yet.',
    ],
    'billable hours invoice' => [
        'requestBillableHoursInvoice',
        [null, '2026-05-01', '2026-05-31'],
        'project_accounting.billable_hours_invoice_requested',
        'billable_hours_invoicing',
        'Billable hours invoicing integration is not implemented yet.',
    ],
    'project profitability accounting' => [
        'requestProfitabilityAccounting',
        [],
        'project_accounting.profitability_requested',
        'project_profitability_accounting',
        'Project profitability accounting integration is not implemented yet.',
    ],
    'project expense posting' => [
        'requestExpensePosting',
        [['amount' => '100.00', 'description' => 'placeholder']],
        'project_accounting.expense_posting_requested',
        'project_expense_posting',
        'Project expense posting integration is not implemented yet.',
    ],
]);

it('rejects project accounting placeholder requests without tenant permission', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create();

    $this->actingAs($actor);

    expect(fn () => app(ProjectAccountingIntegrationService::class)->requestProjectInvoice($project))
        ->toThrow(AuthorizationException::class);

    expect(AuditLog::query()->where('action', 'project_accounting.project_invoice_requested')->exists())->toBeFalse();
});
