<?php

namespace App\Http\Controllers;

use App\Actions\ApproveLeaveRequest;
use App\Actions\CancelLeaveRequest;
use App\Actions\CreateLeaveRequest;
use App\Actions\RejectLeaveRequest;
use App\Actions\ReturnLeaveRequest;
use App\Actions\SubmitLeaveRequest;
use App\Http\Requests\IndexLeaveRequestRequest;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Http\Requests\UpdateLeaveRequestRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\LeaveRequestIndexQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LeaveRequestController extends Controller
{
    public function index(IndexLeaveRequestRequest $request, LeaveRequestIndexQuery $query)
    {
        if ($request->expectsJson()) {
            return LeaveRequestResource::collection($query->paginate($request->validated()));
        }

        $leaveRequests = $query->paginate($request->validated());
        $leaveRequests->load(['employee', 'leaveType', 'approvedBy']);

        return view('leave-requests.index', compact('leaveRequests'));
    }

    public function create()
    {
        Gate::authorize('create', LeaveRequest::class);
        $leaveTypes = LeaveType::forCurrentCompany()->where('status', 'active')->get();
        $employees = Employee::forCurrentCompany()->get(); // Depending on permissions, they might only see themselves

        return view('leave-requests.create', compact('leaveTypes', 'employees'));
    }

    public function store(StoreLeaveRequestRequest $request, CreateLeaveRequest $action)
    {
        $leaveRequest = $action->handle($request->validated(), $request->user())->load(['employee', 'leaveType']);

        if ($request->expectsJson()) {
            return LeaveRequestResource::make($leaveRequest);
        }

        return redirect()->route('leave-requests.show', $leaveRequest->id)->with('success', app()->getLocale() === 'ar' ? 'تم تقديم الطلب بنجاح.' : 'Leave request created successfully.');
    }

    public function show(LeaveRequest $leaveRequest)
    {
        Gate::authorize('view', $leaveRequest);

        if (request()->expectsJson()) {
            return LeaveRequestResource::make($leaveRequest->load(['employee', 'leaveType', 'workflowInstance', 'approvedBy']));
        }

        $leaveRequest->load(['employee', 'leaveType', 'workflowInstance.steps', 'approvedBy']);

        return view('leave-requests.show', compact('leaveRequest'));
    }

    public function edit(LeaveRequest $leaveRequest)
    {
        Gate::authorize('update', $leaveRequest);
        $leaveTypes = LeaveType::forCurrentCompany()->where('status', 'active')->get();
        $employees = Employee::forCurrentCompany()->get();

        return view('leave-requests.edit', compact('leaveRequest', 'leaveTypes', 'employees'));
    }

    public function update(UpdateLeaveRequestRequest $request, LeaveRequest $leaveRequest)
    {
        Gate::authorize('update', $leaveRequest);
        $leaveRequest->update($request->validated());

        if ($request->expectsJson()) {
            return LeaveRequestResource::make($leaveRequest->refresh()->load(['employee', 'leaveType']));
        }

        return redirect()->route('leave-requests.show', $leaveRequest->id)->with('success', app()->getLocale() === 'ar' ? 'تم التحديث بنجاح.' : 'Leave request updated successfully.');
    }

    public function destroy(LeaveRequest $leaveRequest): never
    {
        abort(405);
    }

    public function submit(LeaveRequest $leaveRequest, SubmitLeaveRequest $action)
    {
        $request = $action->handle($leaveRequest, request()->user())->load(['employee', 'leaveType', 'workflowInstance']);

        if (request()->expectsJson()) {
            return LeaveRequestResource::make($request);
        }

        return redirect()->back()->with('success', app()->getLocale() === 'ar' ? 'تم إرسال الطلب للاعتماد.' : 'Request submitted for approval.');
    }

    public function approve(Request $request, LeaveRequest $leaveRequest, ApproveLeaveRequest $action)
    {
        $result = $action->handle($leaveRequest, $request->user(), $request->string('comment')->toString())->load(['employee', 'leaveType']);

        if ($request->expectsJson()) {
            return LeaveRequestResource::make($result);
        }

        return redirect()->back()->with('success', app()->getLocale() === 'ar' ? 'تم الموافقة على الطلب.' : 'Request approved.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest, RejectLeaveRequest $action)
    {
        $result = $action->handle($leaveRequest, $request->user(), $request->string('reason')->toString())->load(['employee', 'leaveType']);

        if ($request->expectsJson()) {
            return LeaveRequestResource::make($result);
        }

        return redirect()->back()->with('success', app()->getLocale() === 'ar' ? 'تم رفض الطلب.' : 'Request rejected.');
    }

    public function cancel(LeaveRequest $leaveRequest, CancelLeaveRequest $action)
    {
        $result = $action->handle($leaveRequest, request()->user())->load(['employee', 'leaveType']);

        if (request()->expectsJson()) {
            return LeaveRequestResource::make($result);
        }

        return redirect()->back()->with('success', app()->getLocale() === 'ar' ? 'تم إلغاء الطلب.' : 'Request cancelled.');
    }

    public function return(LeaveRequest $leaveRequest, ReturnLeaveRequest $action)
    {
        $result = $action->handle($leaveRequest, request()->user(), request()->string('comment')->toString())->load(['employee', 'leaveType']);

        if (request()->expectsJson()) {
            return LeaveRequestResource::make($result);
        }

        return redirect()->back()->with('success', app()->getLocale() === 'ar' ? 'تم إرجاع الطلب للمراجعة.' : 'Request returned for review.');
    }
}
