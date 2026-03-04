<?php

use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\Attendance\ClockController;
use App\Http\Controllers\Attendance\ScheduleController;
use App\Http\Controllers\Employee\EmployeeDocumentController;
use App\Http\Controllers\Employee\EmployeeController;
use App\Http\Controllers\Leave\LeaveController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\DepartmentController;
use App\Http\Controllers\Settings\HolidayController;
use App\Http\Controllers\Settings\JobLevelController;
use App\Http\Controllers\Settings\LeaveTypeController;
use App\Http\Controllers\Settings\PositionController;
use App\Http\Controllers\Settings\ShiftController;
use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Profile (Breeze default — akan di-replace nanti)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Attendance
Route::middleware(['auth'])->prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('index');  // rekap admin
    Route::get('/clock', [ClockController::class, 'index'])->name('clock');
    Route::post('/clock', [ClockController::class, 'store'])->name('clock.store');
});

// Leave
Route::middleware(['auth'])->prefix('leave')->name('leave.')->group(function () {
    Route::get('/', [LeaveController::class, 'index'])->name('index');
    Route::post('/', [LeaveController::class, 'store'])->name('store');
    Route::post('/{leave}/approve', [LeaveController::class, 'approve'])->name('approve');
    Route::post('/{leave}/reject', [LeaveController::class, 'reject'])->name('reject');
});

// Employee Management
Route::middleware(['auth'])->group(function () {
    Route::resource('employees', EmployeeController::class);

    // Employee Documents (nested)
    Route::prefix('employees/{employee}/documents')->name('employees.documents.')->group(function () {
        Route::post('/', [EmployeeDocumentController::class, 'store'])->name('store');
        Route::get('/{document}/download', [EmployeeDocumentController::class, 'download'])->name('download');
        Route::delete('/{document}', [EmployeeDocumentController::class, 'destroy'])->name('destroy');
    });

    // Employee Schedule Assignment (nested)
    Route::prefix('employees/{employee}/schedules')->name('employees.schedules.')->group(function () {
        Route::post('/', [ScheduleController::class, 'store'])->name('store');
        Route::delete('/{schedule}', [ScheduleController::class, 'destroy'])->name('destroy');
    });
});

// Settings
Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
    Route::resource('job-levels', JobLevelController::class)->except(['create', 'edit']);
    Route::resource('departments', DepartmentController::class)->except(['create', 'edit']);
    Route::resource('positions', PositionController::class)->except(['create', 'edit']);
    Route::resource('shifts', ShiftController::class)->except(['create', 'edit']);
    Route::resource('holidays', HolidayController::class)->except(['create', 'edit']);
    Route::resource('leave-types', LeaveTypeController::class)->except(['create', 'edit']);
});

require __DIR__ . '/auth.php';
