<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\DepartmentController;
use App\Http\Controllers\Settings\JobLevelController;
use App\Http\Controllers\Settings\PositionController;
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

// Settings (sistem) — hanya super-admin via 'system-settings' permission
Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
    Route::resource('job-levels', JobLevelController::class)->except(['create', 'edit']);
    Route::resource('departments', DepartmentController::class)->except(['create', 'edit']);
    Route::resource('positions', PositionController::class)->except(['create', 'edit']);
});

require __DIR__ . '/auth.php';
