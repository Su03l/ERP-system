<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDashboardWidgetRequest;
use App\Http\Requests\UpdateDashboardWidgetRequest;
use App\Models\DashboardWidget;
use App\Services\AnalyticsCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardWidgetController extends Controller
{
    public function index(Request $request, AnalyticsCacheService $cache): JsonResponse
    {
        Gate::authorize('dashboard_widgets.view');
        $companyId = $request->user()?->company_id;

        $widgets = $cache->rememberWidgets($companyId, $request->query(), fn () => DashboardWidget::query()
            ->forCurrentCompany()
            ->when($request->query('module'), fn ($query, string $module) => $query->where('module', $module))
            ->orderBy('module')
            ->orderBy('widget_key')
            ->get()
            ->toArray());

        return response()->json(['data' => $widgets]);
    }

    public function store(StoreDashboardWidgetRequest $request): JsonResponse
    {
        $widget = DashboardWidget::query()->create([
            ...$request->validated(),
            'company_id' => $request->user()?->company_id,
        ]);

        return response()->json(['data' => $widget], 201);
    }

    public function show(DashboardWidget $dashboardWidget): JsonResponse
    {
        Gate::authorize('dashboard_widgets.view');
        abort_unless($dashboardWidget->company_id === request()->user()?->company_id, 404);

        return response()->json(['data' => $dashboardWidget]);
    }

    public function update(UpdateDashboardWidgetRequest $request, DashboardWidget $dashboardWidget): JsonResponse
    {
        abort_unless($dashboardWidget->company_id === $request->user()?->company_id, 404);
        $dashboardWidget->update($request->validated());

        return response()->json(['data' => $dashboardWidget->refresh()]);
    }

    public function destroy(DashboardWidget $dashboardWidget): JsonResponse
    {
        Gate::authorize('dashboard_widgets.manage');
        abort_unless($dashboardWidget->company_id === request()->user()?->company_id, 404);
        $dashboardWidget->delete();

        return response()->json(status: 204);
    }
}
