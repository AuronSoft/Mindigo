<?php

use Illuminate\Support\Facades\Route;
use Mindigo\UserManagement\Http\Controllers\UserManagementController;

Route::middleware(['web', 'auth'])
    ->prefix('dashboard/users')
    ->name('users.')
    ->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore', [UserManagementController::class, 'restore'])->name('restore');
    });
