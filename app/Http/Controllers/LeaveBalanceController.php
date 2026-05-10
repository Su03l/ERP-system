<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexLeaveBalanceRequest;
use App\Http\Requests\UpdateLeaveBalanceRequest;
use App\Http\Resources\LeaveBalanceResource;
use App\Models\LeaveBalance;
use App\Services\AuditLogger;
use App\Services\LeaveBalanceIndexQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class LeaveBalanceController extends Controller
{
    public function index(IndexLeaveBalanceRequest $request, LeaveBalanceIndexQuery $query): AnonymousResourceCollection
    {
        return LeaveBalanceResource::collection($query->paginate($request->validated()));
    }

    public function show(LeaveBalance $leaveBalance): LeaveBalanceResource
    {
        Gate::authorize('view', $leaveBalance);

        return LeaveBalanceResource::make($leaveBalance->load(['employee', 'leaveType']));
    }

    public function update(UpdateLeaveBalanceRequest $request, LeaveBalance $leaveBalance, AuditLogger $auditLogger): LeaveBalanceResource
    {
        Gate::authorize('update', $leaveBalance);
        $oldValues = $leaveBalance->attributesToArray();
        $leaveBalance->update($request->validated());
        $auditLogger->log('leave_balance.updated', $leaveBalance, $oldValues, $leaveBalance->refresh()->attributesToArray(), user: $request->user(), company: $leaveBalance->company_id);

        return LeaveBalanceResource::make($leaveBalance->load(['employee', 'leaveType']));
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
