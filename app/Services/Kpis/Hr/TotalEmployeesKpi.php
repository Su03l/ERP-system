<?php

namespace App\Services\Kpis\Hr;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Models\Company;
use App\Models\Employee;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class TotalEmployeesKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('hr.total_employees', 'hr', 'إجمالي الموظفين', 'Total employees', 'employees.view', supportsDateRange: false, defaultDateRange: null);
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        return $this->result($dateRange, Employee::query()->forCompany($company)->count(), unit: 'employees');
    }
}
