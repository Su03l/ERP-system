<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexLeaveTypeRequest;
use App\Http\Requests\StoreLeaveTypeRequest;
use App\Http\Requests\UpdateLeaveTypeRequest;
use App\Http\Resources\LeaveTypeResource;
use App\Models\LeaveType;
use App\Services\AuditLogger;
use App\Services\LeaveTypeIndexQuery;
use Illuminate\Support\Facades\Gate;

class LeaveTypeController extends Controller
{
    public function index(IndexLeaveTypeRequest $request, LeaveTypeIndexQuery $query)
    {
        if ($request->expectsJson()) {
            return LeaveTypeResource::collection($query->paginate($request->validated()));
        }

        $leaveTypes = $query->paginate($request->validated());

        return view('leave-types.index', compact('leaveTypes'));
    }

    public function create()
    {
        Gate::authorize('create', LeaveType::class);

        return view('leave-types.create');
    }

    public function store(StoreLeaveTypeRequest $request, AuditLogger $auditLogger)
    {
        Gate::authorize('create', LeaveType::class);
        $leaveType = LeaveType::create([...$request->validated(), 'company_id' => $request->user()->company_id]);
        $auditLogger->log('leave_type.created', $leaveType, newValues: $leaveType->attributesToArray(), user: $request->user(), company: $leaveType->company_id);

        if ($request->expectsJson()) {
            return LeaveTypeResource::make($leaveType);
        }

        return redirect()->route('leave-types.index')->with('success', app()->getLocale() === 'ar' ? 'تم إضافة نوع الإجازة بنجاح.' : 'Leave type created successfully.');
    }

    public function show(LeaveType $leaveType)
    {
        Gate::authorize('view', $leaveType);

        if (request()->expectsJson()) {
            return LeaveTypeResource::make($leaveType);
        }

        return view('leave-types.show', compact('leaveType'));
    }

    public function edit(LeaveType $leaveType)
    {
        Gate::authorize('update', $leaveType);

        return view('leave-types.edit', compact('leaveType'));
    }

    public function update(UpdateLeaveTypeRequest $request, LeaveType $leaveType, AuditLogger $auditLogger)
    {
        Gate::authorize('update', $leaveType);
        $oldValues = $leaveType->attributesToArray();
        $leaveType->update($request->validated());
        $auditLogger->log('leave_type.updated', $leaveType, $oldValues, $leaveType->refresh()->attributesToArray(), user: $request->user(), company: $leaveType->company_id);

        if ($request->expectsJson()) {
            return LeaveTypeResource::make($leaveType);
        }

        return redirect()->route('leave-types.index')->with('success', app()->getLocale() === 'ar' ? 'تم التحديث بنجاح.' : 'Leave type updated successfully.');
    }

    public function destroy(LeaveType $leaveType, AuditLogger $auditLogger)
    {
        Gate::authorize('delete', $leaveType);
        $oldValues = $leaveType->attributesToArray();
        $auditLogger->log('leave_type.deleted', $leaveType, oldValues: $oldValues, user: request()->user(), company: $leaveType->company_id);
        $leaveType->delete();

        if (request()->expectsJson()) {
            return response()->json(status: 204);
        }

        return redirect()->route('leave-types.index')->with('success', app()->getLocale() === 'ar' ? 'تم الحذف بنجاح.' : 'Leave type deleted successfully.');
    }
}
