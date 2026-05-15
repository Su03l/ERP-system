<?php

namespace App\Services\Kpis\Projects;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Enums\LeadStatus;
use App\Models\Company;
use App\Models\CrmLead;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class LeadsByStatusKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('crm.leads_by_status', 'crm', 'العملاء المحتملون حسب الحالة', 'Leads by status', 'crm_leads.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $values = collect(LeadStatus::cases())
            ->map(fn (LeadStatus $status): array => [
                'status' => $status->value,
                'label' => $status->label(),
                'value' => CrmLead::query()->forCompany($company)->where('status', $status->value)->whereBetween('created_at', [$dateRange->start, $dateRange->end])->count(),
            ])
            ->values()
            ->all();

        return $this->result($dateRange, array_sum(array_column($values, 'value')), unit: 'leads', metadata: ['values' => $values]);
    }
}
