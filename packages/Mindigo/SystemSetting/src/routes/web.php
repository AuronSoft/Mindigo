<?php

use Illuminate\Support\Facades\Route;
use Mindigo\SystemSetting\Http\Controllers\SystemSettingController;

Route::middleware(['web', 'auth', 'admin'])
    ->prefix('dashboard/system-settings')
    ->name('system-settings.')
    ->group(function () {
        Route::get('/', [SystemSettingController::class, 'index'])->name('index');
        Route::put('/', [SystemSettingController::class, 'update'])->name('update');
    });
