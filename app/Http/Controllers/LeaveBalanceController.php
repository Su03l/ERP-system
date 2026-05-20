<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexLeaveBalanceRequest;
use App\Http\Requests\UpdateLeaveBalanceRequest;
use App\Http\Resources\LeaveBalanceResource;
use App\Models\LeaveBalance;
use App\Services\AuditLogger;
use App\Services\LeaveBalanceIndexQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class LeaveBalanceController extends Controller
{
    public function index(IndexLeaveBalanceRequest $request, LeaveBalanceIndexQuery $query)
    {
        if ($request->expectsJson()) {
            return LeaveBalanceResource::collection($query->paginate($request->validated()));
        }

        $leaveBalances = $query->paginate($request->validated());
        $leaveBalances->load(['employee', 'leaveType']);

        return view('leave-balances.index', compact('leaveBalances'));
    }

    public function show(LeaveBalance $leaveBalance)
    {
        Gate::authorize('view', $leaveBalance);

        if (request()->expectsJson()) {
            return LeaveBalanceResource::make($leaveBalance->load(['employee', 'leaveType']));
        }

        $leaveBalance->load(['employee', 'leaveType']);

        return view('leave-balances.show', compact('leaveBalance'));
    }

    public function update(UpdateLeaveBalanceRequest $request, LeaveBalance $leaveBalance, AuditLogger $auditLogger)
    {
        Gate::authorize('update', $leaveBalance);
        $oldValues = $leaveBalance->attributesToArray();
        $leaveBalance->update($request->validated());
        $auditLogger->log('leave_balance.updated', $leaveBalance, $oldValues, $leaveBalance->refresh()->attributesToArray(), user: $request->user(), company: $leaveBalance->company_id);

        if ($request->expectsJson()) {
            return LeaveBalanceResource::make($leaveBalance->load(['employee', 'leaveType']));
        }

        return redirect()->back()->with('success', app()->getLocale() === 'ar' ? 'تم تحديث الرصيد بنجاح.' : 'Leave balance updated successfully.');
    }

    public function store(): never
    {
        abort(405);
    }

    public function destroy(LeaveBalance $leaveBalance): JsonResponse
    {
        abort(405);
    }
}
