<?php

use Illuminate\Support\Facades\Route;
use Mindigo\SupportManagement\Http\Controllers\SupportTicketController;

Route::middleware(['web', 'auth'])
    ->prefix('dashboard/support-tickets')
    ->name('support-tickets.')
    ->group(function () {
        Route::get('/', [SupportTicketController::class, 'index'])->name('index');
        Route::get('/create', [SupportTicketController::class, 'create'])->name('create');
        Route::post('/', [SupportTicketController::class, 'store'])->name('store');
        Route::get('/{supportTicket}', [SupportTicketController::class, 'show'])->name('show');
        Route::post('/{supportTicket}/reply', [SupportTicketController::class, 'reply'])->name('reply');
        Route::put('/{supportTicket}', [SupportTicketController::class, 'update'])->name('update');
        Route::delete('/{supportTicket}', [SupportTicketController::class, 'destroy'])->name('destroy');
    });
