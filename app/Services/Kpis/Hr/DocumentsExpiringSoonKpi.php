<?php

namespace App\Services\Kpis\Hr;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Models\Company;
use App\Services\EmployeeDocumentExpiryQuery;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class DocumentsExpiringSoonKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function __construct(private readonly EmployeeDocumentExpiryQuery $expiryQuery) {}

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('hr.documents_expiring_soon', 'hr', 'مستندات الموظفين قريبة الانتهاء', 'Documents expiring soon', 'employee_documents.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $days = max(0, now()->diffInDays($dateRange->end, false));
        $value = $this->expiryQuery->expiringWithin((int) $days, $company)->count();

        return $this->result($dateRange, $value, unit: 'documents', metadata: ['days' => $days]);
    }
}
