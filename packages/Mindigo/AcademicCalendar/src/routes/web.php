<?php

use Illuminate\Support\Facades\Route;
use Mindigo\AcademicCalendar\Http\Controllers\AdminCalendarExceptionController;

Route::middleware(['web', 'auth', 'role:admin'])
    ->prefix('admin/calendar-exceptions')
    ->name('admin.calendar-exceptions.')
    ->group(function (): void {
        Route::get('/', [AdminCalendarExceptionController::class, 'index'])->name('index');
        Route::post('/', [AdminCalendarExceptionController::class, 'store'])->name('store');
        Route::delete('/{exception}', [AdminCalendarExceptionController::class, 'destroy'])->name('destroy');
    });
