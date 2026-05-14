<?php

namespace App\Services\Kpis\Hr;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Models\Company;
use App\Models\Employee;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class NewHiresKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('hr.new_hires', 'hr', 'التعيينات الجديدة', 'New hires', 'employees.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $value = Employee::query()
            ->forCompany($company)
            ->whereBetween('hire_date', [$dateRange->start->toDateString(), $dateRange->end->toDateString()])
            ->count();

        return $this->result($dateRange, $value, unit: 'employees');
    }
}
