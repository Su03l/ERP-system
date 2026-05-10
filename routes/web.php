<?php

use App\Http\Controllers\AttendanceRecordController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\JobTitleController;
use App\Http\Controllers\LeaveBalanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LeaveTypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->apiResource('employees', EmployeeController::class);
Route::middleware('auth')->apiResource('departments', DepartmentController::class);
Route::middleware('auth')->apiResource('job-titles', JobTitleController::class);
Route::middleware('auth')->post('attendance-records/clock-in', [AttendanceRecordController::class, 'clockIn'])->name('attendance-records.clock-in');
Route::middleware('auth')->post('attendance-records/clock-out', [AttendanceRecordController::class, 'clockOut'])->name('attendance-records.clock-out');
Route::middleware('auth')->apiResource('attendance-records', AttendanceRecordController::class);
Route::middleware('auth')->post('leave-requests/{leave_request}/submit', [LeaveRequestController::class, 'submit'])->name('leave-requests.submit');
Route::middleware('auth')->post('leave-requests/{leave_request}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
Route::middleware('auth')->post('leave-requests/{leave_request}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
Route::middleware('auth')->post('leave-requests/{leave_request}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');
Route::middleware('auth')->post('leave-requests/{leave_request}/return', [LeaveRequestController::class, 'return'])->name('leave-requests.return');
Route::middleware('auth')->apiResource('leave-types', LeaveTypeController::class);
Route::middleware('auth')->apiResource('leave-requests', LeaveRequestController::class);
Route::middleware('auth')->apiResource('leave-balances', LeaveBalanceController::class)->only(['index', 'show', 'update']);
