<?php

namespace App\Services;

use App\DTOs\KpiDateRange;
use App\DTOs\ReportFilter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AnalyticsCacheService
{
    public function rememberKpi(string $key, int|string|null $scopeId, KpiDateRange $dateRange, array $filters, callable $callback): mixed
    {
        return Cache::remember(
            $this->key('kpi', $key, $scopeId, [
                'date_range' => $dateRange->toArray(),
                'filters' => $filters,
            ]),
            now()->addMinutes(10),
            $callback,
        );
    }

    public function rememberWidgets(int|string|null $scopeId, array $filters, callable $callback): mixed
    {
        return Cache::remember($this->key('dashboard_widgets', 'index', $scopeId, $filters), now()->addMinutes(15), $callback);
    }

    public function rememberChart(string $chartKey, int|string|null $scopeId, array $filters, callable $callback): mixed
    {
        return Cache::remember($this->key('chart', $chartKey, $scopeId, $filters), now()->addMinutes(10), $callback);
    }

    public function rememberReportSummary(string $reportKey, ReportFilter $filter, callable $callback): mixed
    {
        return Cache::remember(
            $this->key('report_summary', $reportKey, $filter->companyId ?? 'platform', $filter->toArray()),
            now()->addMinutes(10),
            $callback,
        );
    }

    public function forgetScope(int|string|null $scopeId): void
    {
        Cache::put($this->key('analytics_invalidation', 'scope', $scopeId, []), now()->toDateTimeString(), now()->addDay());
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    public function key(string $type, string $name, int|string|null $scopeId, array $parts): string
    {
        $scope = $scopeId === null ? 'platform' : "company:{$scopeId}";
        $payload = md5(json_encode($this->normalize($parts), JSON_THROW_ON_ERROR));

        return Str::of("analytics:{$type}:{$scope}:{$name}:{$payload}")
            ->replace(['/', '\\', ' '], ':')
            ->toString();
    }

    /**
     * @param  array<string, mixed>  $parts
     * @return array<string, mixed>
     */
    private function normalize(array $parts): array
    {
        ksort($parts);

        foreach ($parts as $key => $value) {
            if (is_array($value)) {
                $parts[$key] = $this->normalize($value);
            }
        }

        return $parts;
    }
}
