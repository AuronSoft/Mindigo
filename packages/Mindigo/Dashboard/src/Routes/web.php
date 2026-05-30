<?php

use Illuminate\Support\Facades\Route;
use Mindigo\Dashboard\Http\Controllers\DashboardController;
use Mindigo\Dashboard\Http\Controllers\SearchController;

Route::middleware(['web', 'auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/search', SearchController::class)->name('dashboard.search');
});
