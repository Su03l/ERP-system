<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexLeaveTypeRequest;
use App\Http\Requests\StoreLeaveTypeRequest;
use App\Http\Requests\UpdateLeaveTypeRequest;
use App\Http\Resources\LeaveTypeResource;
use App\Models\LeaveType;
use App\Services\AuditLogger;
use App\Services\LeaveTypeIndexQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class LeaveTypeController extends Controller
{
    public function index(IndexLeaveTypeRequest $request, LeaveTypeIndexQuery $query): AnonymousResourceCollection
    {
        return LeaveTypeResource::collection($query->paginate($request->validated()));
    }

    public function store(StoreLeaveTypeRequest $request, AuditLogger $auditLogger): LeaveTypeResource
    {
        Gate::authorize('create', LeaveType::class);
        $leaveType = LeaveType::create([...$request->validated(), 'company_id' => $request->user()->company_id]);
        $auditLogger->log('leave_type.created', $leaveType, newValues: $leaveType->attributesToArray(), user: $request->user(), company: $leaveType->company_id);

        return LeaveTypeResource::make($leaveType);
    }

    public function show(LeaveType $leaveType): LeaveTypeResource
    {
        Gate::authorize('view', $leaveType);

        return LeaveTypeResource::make($leaveType);
    }

    public function update(UpdateLeaveTypeRequest $request, LeaveType $leaveType, AuditLogger $auditLogger): LeaveTypeResource
    {
        Gate::authorize('update', $leaveType);
        $oldValues = $leaveType->attributesToArray();
        $leaveType->update($request->validated());
        $auditLogger->log('leave_type.updated', $leaveType, $oldValues, $leaveType->refresh()->attributesToArray(), user: $request->user(), company: $leaveType->company_id);

        return LeaveTypeResource::make($leaveType);
    }

    public function destroy(LeaveType $leaveType, AuditLogger $auditLogger): JsonResponse
    {
        Gate::authorize('delete', $leaveType);
        $oldValues = $leaveType->attributesToArray();
        $auditLogger->log('leave_type.deleted', $leaveType, oldValues: $oldValues, user: request()->user(), company: $leaveType->company_id);
        $leaveType->delete();

        return response()->json(status: 204);
    }
}
