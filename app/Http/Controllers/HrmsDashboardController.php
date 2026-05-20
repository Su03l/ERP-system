<?php

namespace App\Http\Controllers;

use App\DTOs\KpiDateRange;
use App\Models\Company;
use App\Services\KpiRegistry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrmsDashboardController extends Controller
{
    /**
     * Display the HRMS dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->hasPermission('employees.view', $user->company_id), 403);

        $company = Company::find($user->company_id);

        $start = $request->input('date_from', now()->startOfMonth()->toDateString());
        $end = $request->input('date_to', now()->endOfMonth()->toDateString());

        try {
            $dateRange = KpiDateRange::fromDates($start, $end);
        } catch (\InvalidArgumentException $e) {
            $dateRange = KpiDateRange::fromDates(
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString()
            );
        }

        $registry = KpiRegistry::default();

        $kpis = [
            'total_employees' => 'hr.total_employees',
            'active_employees' => 'hr.active_employees',
            'new_hires' => 'hr.new_hires',
            'employees_by_department' => 'hr.employees_by_department',
            'documents_expiring_soon' => 'hr.documents_expiring_soon',
        ];

        $resolvedData = [];

        foreach ($kpis as $key => $resolverId) {
            try {
                $resolvedData[$key] = $registry->resolve($resolverId, $company, $dateRange);
            } catch (\Exception $e) {
                $resolvedData[$key] = null;
            }
        }

        return view('hrms.dashboard', [
            'resolvedData' => $resolvedData,
            'dateFrom' => $dateRange->start->toDateString(),
            'dateTo' => $dateRange->end->toDateString(),
        ]);
    }
}
