<?php

namespace App\Services\Kpis\Projects;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Enums\ProjectStatus;
use App\Models\Company;
use App\Models\Project;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class CompletedProjectsKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('projects.completed_projects', 'projects', 'المشاريع المكتملة', 'Completed projects', 'projects.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $value = Project::query()->forCompany($company)->where('status', ProjectStatus::Completed->value)->whereBetween('created_at', [$dateRange->start, $dateRange->end])->count();

        return $this->result($dateRange, $value, unit: 'projects');
    }
}
