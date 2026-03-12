<?php

use App\Http\Controllers\CoreHR\DepartmentController;
use App\Http\Controllers\CoreHR\EmployeeController;
use App\Http\Controllers\CoreHR\JobLevelController;
use App\Http\Controllers\CoreHR\PositionController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // Core HR
    Route::resource('employees', EmployeeController::class)->except(['destroy']);
    Route::resource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('positions', PositionController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('job-levels', JobLevelController::class)->only(['index', 'store', 'update', 'destroy']);
});

require __DIR__ . '/settings.php';
