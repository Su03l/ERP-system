<?php

namespace App\Http\Controllers;

use App\DTOs\KpiDateRange;
use App\Http\Requests\QueryKpiRequest;
use App\Services\AnalyticsCacheService;
use App\Services\KpiRegistry;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class KpiController extends Controller
{
    public function index(QueryKpiRequest $request, KpiRegistry $registry): JsonResponse
    {
        $definitions = collect(KpiRegistry::default()->available())
            ->filter(fn (array $definition): bool => $this->canViewDefinition($definition))
            ->values()
            ->all();

        return response()->json(['data' => $definitions]);
    }

    public function query(QueryKpiRequest $request, AnalyticsCacheService $cache): JsonResponse
    {
        $user = $request->user();
        $company = $user?->company;
        $dateRange = KpiDateRange::fromDates(
            $request->string('date_from')->toString() ?: CarbonImmutable::now()->startOfMonth()->toDateString(),
            $request->string('date_to')->toString() ?: CarbonImmutable::now()->toDateString(),
        );
        $keys = $request->validated('keys') ?: collect(KpiRegistry::default()->available())->pluck('key')->all();

        $results = collect($keys)
            ->map(function (string $key) use ($cache, $company, $dateRange): ?array {
                $definition = KpiRegistry::default()->definition($key);

                if (! $this->canViewDefinition($definition->toArray()) || $company === null) {
                    return null;
                }

                return $cache->rememberKpi($key, $company->id, $dateRange, [], fn (): array => KpiRegistry::default()
                    ->resolve($key, $company, $dateRange)
                    ->toArray());
            })
            ->filter()
            ->values()
            ->all();

        return response()->json(['data' => $results]);
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function canViewDefinition(array $definition): bool
    {
        $permission = $definition['required_permission'] ?? null;

        return $permission === null || Gate::allows($permission);
    }
}
