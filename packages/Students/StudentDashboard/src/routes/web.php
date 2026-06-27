<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentDashboard\Http\Controllers\DashboardController;

Route::middleware(['web', 'auth'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
    });
});
