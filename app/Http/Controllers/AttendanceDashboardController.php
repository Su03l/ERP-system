<?php

namespace App\Http\Controllers;

use App\Services\KpiRegistry;
use Illuminate\Http\Request;

class AttendanceDashboardController extends Controller
{
    public function index(Request $request, KpiRegistry $kpiRegistry)
    {
        $companyId = $request->user()->company_id;

        // This is a minimal dashboard for attendance metrics.
        // It fetches basic attendance data from the KpiRegistry if available.
        // E.g., 'hr.attendance_rate', 'hr.total_absences'

        $kpis = $kpiRegistry->resolveMultiple([
            'hr.attendance_rate',
            'hr.total_absences',
            'hr.average_overtime',
        ], $companyId);

        return view('attendance.dashboard', compact('kpis'));
    }
}
