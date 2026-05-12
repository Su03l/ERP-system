<?php

namespace App\Http\Controllers;

use App\Actions\ClockInEmployee;
use App\Actions\ClockOutEmployee;
use App\Actions\CreateAttendanceRecord;
use App\Actions\DeleteAttendanceRecord;
use App\Actions\UpdateAttendanceRecord;
use App\Http\Requests\IndexAttendanceRecordRequest;
use App\Http\Requests\ManualClockAttendanceRequest;
use App\Http\Requests\StoreAttendanceRecordRequest;
use App\Http\Requests\UpdateAttendanceRecordRequest;
use App\Http\Resources\AttendanceRecordResource;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Services\AttendanceIndexQuery;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class AttendanceRecordController extends Controller
{
    // index used for  ==
    public function index(IndexAttendanceRecordRequest $request, AttendanceIndexQuery $attendanceIndexQuery): AnonymousResourceCollection
    {
        return AttendanceRecordResource::collection($attendanceIndexQuery->paginate($request->validated()));
    }

    public function store(StoreAttendanceRecordRequest $request, CreateAttendanceRecord $createAttendanceRecord): AttendanceRecordResource
    {
        $attendanceRecord = $createAttendanceRecord->handle($request->validated(), $request->user());

        return AttendanceRecordResource::make($attendanceRecord->load(['employee.department', 'employee.jobTitle']));
    }

    public function show(AttendanceRecord $attendanceRecord): AttendanceRecordResource
    {
        Gate::authorize('view', $attendanceRecord);

        return AttendanceRecordResource::make($attendanceRecord->load(['employee.department', 'employee.jobTitle']));
    }

    public function update(
        UpdateAttendanceRecordRequest $request,
        AttendanceRecord $attendanceRecord,
        UpdateAttendanceRecord $updateAttendanceRecord,
    ): AttendanceRecordResource {
        $attendanceRecord = $updateAttendanceRecord->handle($attendanceRecord, $request->validated(), $request->user());

        return AttendanceRecordResource::make($attendanceRecord->load(['employee.department', 'employee.jobTitle']));
    }

    public function destroy(AttendanceRecord $attendanceRecord, DeleteAttendanceRecord $deleteAttendanceRecord): JsonResponse
    {
        $deleteAttendanceRecord->handle($attendanceRecord, request()->user());

        return response()->json(status: 204);
    }

    public function clockIn(ManualClockAttendanceRequest $request, ClockInEmployee $clockInEmployee): AttendanceRecordResource
    {
        $employee = Employee::query()
            ->forCurrentCompany()
            ->findOrFail((int) $request->validated('employee_id'));

        $attendanceRecord = $clockInEmployee->handle(
            employee: $employee,
            clockInAt: CarbonImmutable::parse((string) $request->validated('clock_at')),
            ipAddress: $request->validated('ip_address'),
            actor: $request->user(),
        );

        return AttendanceRecordResource::make($attendanceRecord->load(['employee.department', 'employee.jobTitle']));
    }

    public function clockOut(ManualClockAttendanceRequest $request, ClockOutEmployee $clockOutEmployee): AttendanceRecordResource
    {
        $employee = Employee::query()
            ->forCurrentCompany()
            ->findOrFail((int) $request->validated('employee_id'));

        $attendanceRecord = $clockOutEmployee->handle(
            employee: $employee,
            clockOutAt: CarbonImmutable::parse((string) $request->validated('clock_at')),
            ipAddress: $request->validated('ip_address'),
            actor: $request->user(),
        );

        return AttendanceRecordResource::make($attendanceRecord->load(['employee.department', 'employee.jobTitle']));
    }
}
