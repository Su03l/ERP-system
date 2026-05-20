<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Services\KpiRegistry;
use Illuminate\Http\Request;

class LeaveDashboardController extends Controller
{
    public function index(Request $request, KpiRegistry $kpiRegistry)
    {
        $companyId = $request->user()->company_id;

        $kpis = $kpiRegistry->resolveMultiple([
            'hr.active_leaves',
            'hr.pending_leave_requests',
        ], $companyId);

        // Fetch some pending requests for the dashboard
        $pendingRequests = LeaveRequest::query()
            ->with(['employee'])
            ->forCurrentCompany()
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        return view('leave.dashboard', compact('kpis', 'pendingRequests'));
    }
}
