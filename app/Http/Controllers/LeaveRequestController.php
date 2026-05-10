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
use App\Models\LeaveRequest;
use App\Services\LeaveRequestIndexQuery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class LeaveRequestController extends Controller
{
    public function index(IndexLeaveRequestRequest $request, LeaveRequestIndexQuery $query): AnonymousResourceCollection
    {
        return LeaveRequestResource::collection($query->paginate($request->validated()));
    }

    public function store(StoreLeaveRequestRequest $request, CreateLeaveRequest $action): LeaveRequestResource
    {
        return LeaveRequestResource::make($action->handle($request->validated(), $request->user())->load(['employee', 'leaveType']));
    }

    public function show(LeaveRequest $leaveRequest): LeaveRequestResource
    {
        Gate::authorize('view', $leaveRequest);

        return LeaveRequestResource::make($leaveRequest->load(['employee', 'leaveType', 'workflowInstance', 'approvedBy']));
    }

    public function update(UpdateLeaveRequestRequest $request, LeaveRequest $leaveRequest): LeaveRequestResource
    {
        Gate::authorize('update', $leaveRequest);
        $leaveRequest->update($request->validated());

        return LeaveRequestResource::make($leaveRequest->refresh()->load(['employee', 'leaveType']));
    }

    public function destroy(LeaveRequest $leaveRequest): never
    {
        abort(405);
    }

    public function submit(LeaveRequest $leaveRequest, SubmitLeaveRequest $action): LeaveRequestResource
    {
        return LeaveRequestResource::make($action->handle($leaveRequest, request()->user())->load(['employee', 'leaveType', 'workflowInstance']));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest, ApproveLeaveRequest $action): LeaveRequestResource
    {
        return LeaveRequestResource::make($action->handle($leaveRequest, $request->user(), $request->string('comment')->toString())->load(['employee', 'leaveType']));
    }

    public function reject(Request $request, LeaveRequest $leaveRequest, RejectLeaveRequest $action): LeaveRequestResource
    {
        return LeaveRequestResource::make($action->handle($leaveRequest, $request->user(), $request->string('reason')->toString())->load(['employee', 'leaveType']));
    }

    public function cancel(LeaveRequest $leaveRequest, CancelLeaveRequest $action): LeaveRequestResource
    {
        return LeaveRequestResource::make($action->handle($leaveRequest, request()->user())->load(['employee', 'leaveType']));
    }

    public function return(LeaveRequest $leaveRequest, ReturnLeaveRequest $action): LeaveRequestResource
    {
        return LeaveRequestResource::make($action->handle($leaveRequest, request()->user(), request()->string('comment')->toString())->load(['employee', 'leaveType']));
    }
}
