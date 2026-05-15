<?php

namespace App\Http\Controllers;

use App\DTOs\ReportFilter;
use App\Http\Requests\ExecuteReportRequest;
use App\Http\Requests\ReportExportRequest;
use App\Services\ReportExportService;
use App\Services\ReportRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function index(): JsonResponse
    {
        Gate::authorize('reports.view');

        return response()->json(['data' => ReportRegistry::default()->available()]);
    }

    public function execute(ExecuteReportRequest $request): JsonResponse
    {
        Gate::authorize('reports.run');

        $filter = ReportFilter::fromArray($request->validated(), $request->user()?->company_id);
        $definition = ReportRegistry::default()->definition($request->string('report_key')->toString());

        return response()->json([
            'data' => [
                'report' => $definition->toArray(),
                'filters' => $filter->toArray(),
                'resolver_class' => $definition->resolverClass,
            ],
        ]);
    }

    public function export(ReportExportRequest $request, ReportExportService $exports): JsonResponse
    {
        $filter = ReportFilter::fromArray($request->validated(), $request->user()?->company_id);
        $job = $exports->request(
            $request->string('report_key')->toString(),
            $filter,
            $request->user(),
            $request->boolean('queued', true),
        );

        return response()->json(['data' => $job->toArray()], 202);
    }
}
