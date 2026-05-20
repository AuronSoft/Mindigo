<?php

use Illuminate\Support\Facades\Route;
use Mindigo\Dashboard\Http\Controllers\DashboardController;

Route::middleware(['web', 'auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
