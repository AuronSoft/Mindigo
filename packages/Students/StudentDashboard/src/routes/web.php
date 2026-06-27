<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentDashboard\Http\Controllers\DashboardController;

Route::middleware(['web', 'auth', 'role:student|admin'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    });
