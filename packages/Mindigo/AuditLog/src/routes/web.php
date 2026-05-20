<?php

use Illuminate\Support\Facades\Route;
use Mindigo\AuditLog\Http\Controllers\AuditLogController;

Route::middleware(['web', 'auth', 'admin'])
    ->prefix('dashboard/audit-logs')
    ->name('audit-logs.')
    ->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
        Route::get('/{auditLog}', [AuditLogController::class, 'show'])->name('show');
    });
