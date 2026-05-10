<?php

use App\Http\Controllers\AttendanceRecordController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\JobTitleController;
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
