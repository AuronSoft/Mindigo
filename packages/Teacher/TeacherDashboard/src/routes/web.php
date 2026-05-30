<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherDashboard\Http\Controllers\TeacherDashboardController;

Route::middleware(['web', 'auth', 'role:teacher|admin'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/', [TeacherDashboardController::class, 'index'])->name('dashboard');
    });
