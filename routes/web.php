<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Settings\HolidayController;
use App\Http\Controllers\Settings\SystemSettingController;
use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\Attendance\RecapController;
use App\Http\Controllers\Attendance\ExceptionController;
use App\Http\Controllers\Attendance\ScheduleController;
use App\Http\Controllers\Attendance\ShiftController;
use App\Http\Controllers\Attendance\TimesheetController;
use App\Http\Controllers\CoreHR\DepartmentController;
use App\Http\Controllers\CoreHR\EmployeeController;
use App\Http\Controllers\CoreHR\JobLevelController;
use App\Http\Controllers\CoreHR\PositionController;
use App\Http\Controllers\Leave\LeaveBalanceController;
use App\Http\Controllers\Leave\LeaveRequestController;
use App\Http\Controllers\Leave\LeaveTypeController;
use App\Http\Controllers\Payroll\EmployeePayrollConfigController;
use App\Http\Controllers\Payroll\PayrollComponentController;
use App\Http\Controllers\Payroll\PayrollController;
use App\Http\Controllers\Payroll\SalaryGradeController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Core HR
    Route::resource('employees', EmployeeController::class)->except(['destroy']);
    Route::resource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('positions', PositionController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('job-levels', JobLevelController::class)->only(['index', 'store', 'update', 'destroy']);

    // Attendance
    Route::prefix('attendance')->group(function () {
        Route::get('clock', [AttendanceController::class, 'clockInOut'])->name('attendance.clock');
        Route::post('clock', [AttendanceController::class, 'clock']);

        Route::resource('shifts', ShiftController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::get('schedules', [ScheduleController::class, 'index'])->name('attendance.schedules.index');
        Route::get('schedules/generate', [ScheduleController::class, 'generateForm'])->name('attendance.schedules.generate');
        Route::post('schedules/generate', [ScheduleController::class, 'generate']);

        Route::get('timesheet', [TimesheetController::class, 'index'])->name('attendance.timesheet');
        Route::get('timesheet/export', [TimesheetController::class, 'export'])->name('attendance.timesheet.export');

        Route::get('exceptions', [ExceptionController::class, 'index'])->name('attendance.exceptions.index');
        Route::put('exceptions/{exception}/approve', [ExceptionController::class, 'approve'])->name('attendance.exceptions.approve');
        Route::put('exceptions/{exception}/reject', [ExceptionController::class, 'reject'])->name('attendance.exceptions.reject');
        Route::post('exceptions/half-day', [ExceptionController::class, 'storeHalfDay'])->name('attendance.exceptions.half-day');
    });

    // Leave Management
    Route::prefix('leave')->name('leave.')->group(function () {
        Route::get('balance', [LeaveBalanceController::class, 'index'])->name('balance');

        Route::resource('types', LeaveTypeController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::get('requests', [LeaveRequestController::class, 'index'])->name('requests.index');
        Route::get('requests/create', [LeaveRequestController::class, 'create'])->name('requests.create');
        Route::get('requests/approval', [LeaveRequestController::class, 'approval'])->name('requests.approval');
        Route::post('requests', [LeaveRequestController::class, 'store'])->name('requests.store');
        Route::put('requests/{request}/approve', [LeaveRequestController::class, 'approve'])->name('requests.approve');
        Route::put('requests/{request}/reject', [LeaveRequestController::class, 'reject'])->name('requests.reject');
        Route::put('requests/{request}/cancel', [LeaveRequestController::class, 'cancel'])->name('requests.cancel');

        Route::get('half-day/create', fn() => inertia('Leave/HalfDayPermit/Create'))->name('half-day.create');
    });

    // Payroll
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::resource('components', PayrollComponentController::class)->only(['index', 'store', 'update', 'destroy']);

        // Employee payroll configs (PUT/DELETE only — index/store via employees nested)
        Route::put('configs/{config}', [EmployeePayrollConfigController::class, 'update'])->name('configs.update');
        Route::delete('configs/{config}', [EmployeePayrollConfigController::class, 'destroy'])->name('configs.destroy');

        // Payroll runs — read (all HR roles)
        Route::get('/', [PayrollController::class, 'index'])->name('index');
        Route::get('/{payrollRun}/report', [PayrollController::class, 'report'])->name('report');
        Route::get('/{payrollRun}/slip/{employee}', [PayrollController::class, 'slip'])->name('slip');

        // Employee self-service: view own latest slip
        Route::get('/my-slip', [PayrollController::class, 'mySlip'])->name('my-slip');

        // Payroll mutations — restricted to hr & super-admin
        Route::middleware('role:super-admin|hr')->group(function () {
            Route::post('/calculate', [PayrollController::class, 'calculate'])->name('calculate');
            Route::put('/{payrollRun}/approve', [PayrollController::class, 'approve'])->name('approve');
            Route::put('/{payrollRun}/paid', [PayrollController::class, 'markPaid'])->name('paid');
            Route::get('/{payrollRun}/export', [PayrollController::class, 'export'])->name('export');
        });
    });

    // Employee nested payroll configs
    Route::post('employees/{employee}/payroll-configs', [EmployeePayrollConfigController::class, 'store'])
        ->name('employees.payroll-configs.store');
    Route::get('employees/{employee}/payroll-configs', [EmployeePayrollConfigController::class, 'index'])
        ->name('employees.payroll-configs.index');

    // Salary Grades
    Route::resource('payroll/salary-grades', SalaryGradeController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('payroll.salary-grades');
    Route::post('payroll/salary-grades/apply', [SalaryGradeController::class, 'applyToEmployee'])
        ->name('payroll.salary-grades.apply');

    Route::resource('settings/holidays', HolidayController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('settings.holidays');

    // Attendance Recap
    Route::get('attendance/recap', [RecapController::class, 'index'])->name('attendance.recap');
    Route::get('attendance/recap/export', [RecapController::class, 'export'])->name('attendance.recap.export');

    // System Settings
    Route::get('settings/system', [SystemSettingController::class, 'index'])->name('settings.system');
    Route::put('settings/system', [SystemSettingController::class, 'update'])->name('settings.system.update');
});

require __DIR__ . '/settings.php';
