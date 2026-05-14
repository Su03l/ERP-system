<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Enums\ProjectStatus;
use App\Enums\ProjectTaskStatus;
use App\Models\Company;
use App\Models\CrmLead;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTimeLog;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ProjectCrmMetricsService
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    /**
     * @param  array{date_from?: string, date_until?: string}  $filters
     * @return array<string, mixed>
     */
    public function forCompany(Company|int|null $company = null, array $filters = []): array
    {
        $companyId = $this->companyId($company);
        $dateFrom = isset($filters['date_from']) ? Carbon::parse($filters['date_from'])->startOfDay() : null;
        $dateUntil = isset($filters['date_until']) ? Carbon::parse($filters['date_until'])->endOfDay() : null;

        $projects = Project::query()->forCompany($companyId);
        $this->applyCreatedRange($projects, $dateFrom, $dateUntil);

        $timeLogs = ProjectTimeLog::query()->forCompany($companyId);
        $this->applyLogRange($timeLogs, $dateFrom, $dateUntil);

        $leadsByStatus = collect(LeadStatus::cases())
            ->map(fn (LeadStatus $status): array => [
                'key' => $status->value,
                'label' => $status->label(),
                'value' => (int) CrmLead::query()
                    ->forCompany($companyId)
                    ->where('status', $status->value)
                    ->when($dateFrom, fn ($query) => $query->where('created_at', '>=', $dateFrom))
                    ->when($dateUntil, fn ($query) => $query->where('created_at', '<=', $dateUntil))
                    ->count(),
            ])
            ->values()
            ->all();

        return [
            'company_id' => $companyId,
            'date_from' => $dateFrom?->toDateString(),
            'date_until' => $dateUntil?->toDateString(),
            'metrics' => [
                'total_projects' => $this->metric('total_projects', __('crm.metrics.total_projects'), (int) (clone $projects)->count()),
                'active_projects' => $this->metric('active_projects', ProjectStatus::Active->label(), (int) (clone $projects)->where('status', ProjectStatus::Active->value)->count()),
                'completed_projects' => $this->metric('completed_projects', ProjectStatus::Completed->label(), (int) (clone $projects)->where('status', ProjectStatus::Completed->value)->count()),
                'overdue_tasks' => $this->metric('overdue_tasks', __('crm.metrics.overdue_tasks'), $this->overdueTasks($companyId)),
                'project_profitability' => $this->metric('project_profitability', __('crm.metrics.project_profitability'), null, ['placeholder' => true]),
                'total_logged_hours' => $this->metric('total_logged_hours', __('crm.metrics.total_logged_hours'), round(((int) (clone $timeLogs)->sum('total_minutes')) / 60, 2)),
                'billable_hours' => $this->metric('billable_hours', __('crm.metrics.billable_hours'), round(((int) (clone $timeLogs)->where('is_billable', true)->sum('total_minutes')) / 60, 2)),
                'lead_conversion' => $this->metric('lead_conversion', __('crm.metrics.lead_conversion'), null, ['placeholder' => true]),
            ],
            'groups' => [
                'leads_by_status' => $leadsByStatus,
            ],
        ];
    }

    private function companyId(Company|int|null $company): int
    {
        if ($company instanceof Company) {
            return (int) $company->id;
        }

        return (int) ($company ?? $this->tenantContext->companyId() ?? 0);
    }

    private function overdueTasks(int $companyId): int
    {
        return (int) ProjectTask::query()
            ->forCompany($companyId)
            ->whereDate('due_date', '<', Carbon::today())
            ->where('status', '!=', ProjectTaskStatus::Completed->value)
            ->count();
    }

    /** @param Builder<Project> $query */
    private function applyCreatedRange(Builder $query, ?Carbon $dateFrom, ?Carbon $dateUntil): void
    {
        $query
            ->when($dateFrom, fn ($query) => $query->where('created_at', '>=', $dateFrom))
            ->when($dateUntil, fn ($query) => $query->where('created_at', '<=', $dateUntil));
    }

    /** @param Builder<ProjectTimeLog> $query */
    private function applyLogRange(Builder $query, ?Carbon $dateFrom, ?Carbon $dateUntil): void
    {
        $query
            ->when($dateFrom, fn ($query) => $query->whereDate('log_date', '>=', $dateFrom->toDateString()))
            ->when($dateUntil, fn ($query) => $query->whereDate('log_date', '<=', $dateUntil->toDateString()));
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array{key: string, label: string, value: mixed, metadata: array<string, mixed>}
     */
    private function metric(string $key, string $label, mixed $value, array $metadata = []): array
    {
        return compact('key', 'label', 'value', 'metadata');
    }
}
