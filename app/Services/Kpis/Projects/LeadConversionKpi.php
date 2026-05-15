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

class LeadConversionKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('crm.lead_conversion', 'crm', 'تحويل العملاء المحتملين', 'Lead conversion', 'crm_leads.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $query = CrmLead::query()->forCompany($company)->whereBetween('created_at', [$dateRange->start, $dateRange->end]);
        $total = (clone $query)->count();
        $converted = (clone $query)->where('status', LeadStatus::Converted->value)->count();
        $value = $this->percentage($converted, $total);

        return $this->result($dateRange, $value, formattedValue: "{$value}%", unit: 'percent', metadata: ['converted' => $converted, 'total' => $total]);
    }
}
