<?php

namespace App\Http\Controllers;

use App\Models\PayrollRun;
use Illuminate\Http\Request;

class PayrollDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Must have some payroll permissions to view the dashboard
        if (! $request->user()->can('viewAny', PayrollRun::class)) {
            abort(403, 'Unauthorized action.');
        }

        // Get latest payroll runs for the current company
        $latestRuns = PayrollRun::forCurrentCompany()
            ->with('payrollPeriod')
            ->latest('id')
            ->take(5)
            ->get();

        // Calculate KPI metrics from approved/paid runs this year (or overall)
        $approvedRuns = PayrollRun::forCurrentCompany()
            ->whereIn('status', ['approved', 'paid'])
            ->get();

        $totalPayrollCost = $approvedRuns->sum('total_net_amount');
        $totalAllowances = $approvedRuns->sum('total_allowances');
        $totalDeductions = $approvedRuns->sum('total_deductions');

        $totalEmployeesPaid = $approvedRuns->sum('total_employees');
        $averageSalary = $totalEmployeesPaid > 0 ? ($totalPayrollCost / $totalEmployeesPaid) : 0;

        return view('payroll.dashboard', compact(
            'latestRuns',
            'totalPayrollCost',
            'totalAllowances',
            'totalDeductions',
            'averageSalary'
        ));
    }
}
