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
use Illuminate\Support\Facades\Gate;

class AttendanceRecordController extends Controller
{
    public function index(IndexAttendanceRecordRequest $request, AttendanceIndexQuery $attendanceIndexQuery)
    {
        if ($request->expectsJson()) {
            return AttendanceRecordResource::collection($attendanceIndexQuery->paginate($request->validated()));
        }

        $records = $attendanceIndexQuery->paginate($request->validated());

        return view('attendance.index', compact('records'));
    }

    public function create()
    {
        Gate::authorize('create', AttendanceRecord::class);
        $employees = Employee::forCurrentCompany()->get();

        return view('attendance.create', compact('employees'));
    }

    public function store(StoreAttendanceRecordRequest $request, CreateAttendanceRecord $createAttendanceRecord)
    {
        $attendanceRecord = $createAttendanceRecord->handle($request->validated(), $request->user());

        if ($request->expectsJson()) {
            return AttendanceRecordResource::make($attendanceRecord->load(['employee.department', 'employee.jobTitle']));
        }

        return redirect()->route('attendance-records.index')->with('success', app()->getLocale() === 'ar' ? 'تم تسجيل الحضور بنجاح.' : 'Attendance record created successfully.');
    }

    public function show(AttendanceRecord $attendanceRecord)
    {
        Gate::authorize('view', $attendanceRecord);

        if (request()->expectsJson()) {
            return AttendanceRecordResource::make($attendanceRecord->load(['employee.department', 'employee.jobTitle']));
        }

        return view('attendance.show', compact('attendanceRecord'));
    }

    public function edit(AttendanceRecord $attendanceRecord)
    {
        Gate::authorize('update', $attendanceRecord);
        $employees = Employee::forCurrentCompany()->get();

        return view('attendance.edit', compact('attendanceRecord', 'employees'));
    }

    public function update(
        UpdateAttendanceRecordRequest $request,
        AttendanceRecord $attendanceRecord,
        UpdateAttendanceRecord $updateAttendanceRecord,
    ) {
        $attendanceRecord = $updateAttendanceRecord->handle($attendanceRecord, $request->validated(), $request->user());

        if ($request->expectsJson()) {
            return AttendanceRecordResource::make($attendanceRecord->load(['employee.department', 'employee.jobTitle']));
        }

        return redirect()->route('attendance-records.index')->with('success', app()->getLocale() === 'ar' ? 'تم تحديث السجل بنجاح.' : 'Attendance record updated successfully.');
    }

    public function destroy(AttendanceRecord $attendanceRecord, DeleteAttendanceRecord $deleteAttendanceRecord)
    {
        $deleteAttendanceRecord->handle($attendanceRecord, request()->user());

        if (request()->expectsJson()) {
            return response()->json(status: 204);
        }

        return redirect()->route('attendance-records.index')->with('success', app()->getLocale() === 'ar' ? 'تم الحذف بنجاح.' : 'Attendance record deleted successfully.');
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
