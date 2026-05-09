<?php

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
