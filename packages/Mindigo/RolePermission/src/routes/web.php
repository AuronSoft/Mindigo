<?php

use Illuminate\Support\Facades\Route;
use Mindigo\RolePermission\Http\Controllers\RolePermissionController;

Route::middleware(['web', 'auth', 'role:admin'])
    ->prefix('dashboard/role-permissions')
    ->name('role-permissions.')
    ->group(function () {
        Route::get('/', [RolePermissionController::class, 'index'])->name('index');
        Route::put('/', [RolePermissionController::class, 'update'])->name('update');
    });
