<?php

namespace App\Services\Kpis\Projects;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Enums\ProjectTaskStatus;
use App\Models\Company;
use App\Models\ProjectTask;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class OverdueTasksKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('projects.overdue_tasks', 'projects', 'المهام المتأخرة', 'Overdue tasks', 'project_tasks.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $value = ProjectTask::query()
            ->forCompany($company)
            ->where('status', '!=', ProjectTaskStatus::Completed->value)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->count();

        return $this->result($dateRange, $value, unit: 'tasks');
    }
}
