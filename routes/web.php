<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->apiResource('employees', EmployeeController::class);
Route::middleware('auth')->apiResource('departments', DepartmentController::class);
