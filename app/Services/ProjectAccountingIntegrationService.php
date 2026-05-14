<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use LogicException;

class ProjectAccountingIntegrationService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function requestProjectInvoice(Project $project, ?User $actor = null): never
    {
        $this->requestPlaceholder(
            project: $project,
            action: 'project_accounting.project_invoice_requested',
            integration: 'project_invoicing',
            message: 'Project invoicing accounting integration is not implemented yet.',
            actor: $actor,
        );
    }

    public function requestBillableHoursInvoice(Project $project, ?User $actor = null, ?string $dateFrom = null, ?string $dateUntil = null): never
    {
        $this->requestPlaceholder(
            project: $project,
            action: 'project_accounting.billable_hours_invoice_requested',
            integration: 'billable_hours_invoicing',
            message: 'Billable hours invoicing integration is not implemented yet.',
            actor: $actor,
            metadata: [
                'date_from' => $dateFrom,
                'date_until' => $dateUntil,
            ],
        );
    }

    public function requestProfitabilityAccounting(Project $project, ?User $actor = null): never
    {
        $this->requestPlaceholder(
            project: $project,
            action: 'project_accounting.profitability_requested',
            integration: 'project_profitability_accounting',
            message: 'Project profitability accounting integration is not implemented yet.',
            actor: $actor,
        );
    }

    /** @param array<string, mixed> $expensePayload */
    public function requestExpensePosting(Project $project, array $expensePayload = [], ?User $actor = null): never
    {
        $this->requestPlaceholder(
            project: $project,
            action: 'project_accounting.expense_posting_requested',
            integration: 'project_expense_posting',
            message: 'Project expense posting integration is not implemented yet.',
            actor: $actor,
            metadata: ['expense_payload_keys' => array_keys($expensePayload)],
        );
    }

    /** @param array<string, mixed> $metadata */
    private function requestPlaceholder(Project $project, string $action, string $integration, string $message, ?User $actor, array $metadata = []): never
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User || $actor->company_id !== $project->company_id || ! $actor->hasPermission('projects.update', $project->company_id)) {
            throw new AuthorizationException('You are not authorized to request project accounting integration.');
        }

        $this->auditLogger->log(
            action: $action,
            auditable: $project,
            metadata: [
                'status' => 'accounting_module_not_ready',
                'integration' => $integration,
                ...$metadata,
            ],
            user: $actor,
            company: $project->company_id,
        );

        throw new LogicException($message);
    }
}
