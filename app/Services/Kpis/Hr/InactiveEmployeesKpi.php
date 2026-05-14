<?php

namespace App\Services\Kpis\Hr;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Enums\EmployeeStatus;
use App\Models\Company;
use App\Models\Employee;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class InactiveEmployeesKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('hr.inactive_employees', 'hr', 'الموظفون غير النشطين', 'Inactive employees', 'employees.view', supportsDateRange: false, defaultDateRange: null);
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $value = Employee::query()->forCompany($company)->where('employment_status', EmployeeStatus::Inactive->value)->count();

        return $this->result($dateRange, $value, unit: 'employees');
    }
}
