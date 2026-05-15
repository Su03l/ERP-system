<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChartDataRequest;
use App\Services\AnalyticsCacheService;
use App\Services\ChartDataService;
use Illuminate\Http\JsonResponse;

class ChartDataController extends Controller
{
    public function store(ChartDataRequest $request, ChartDataService $charts, AnalyticsCacheService $cache): JsonResponse
    {
        $data = $request->validated();
        $companyId = $request->user()?->company_id;

        $chart = $cache->rememberChart(
            $data['metadata']['key'] ?? $data['type'],
            $companyId,
            $data,
            fn () => $charts->make(
                type: $data['type'],
                labels: $data['labels'] ?? [],
                datasets: $data['datasets'] ?? [],
                series: $data['series'] ?? [],
                totals: $data['totals'] ?? [],
                metadata: $data['metadata'] ?? [],
            )->toArray(),
        );

        return response()->json(['data' => $chart]);
    }
}
