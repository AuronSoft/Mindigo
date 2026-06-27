<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentSchedule\Http\Controllers\ScheduleController;

Route::middleware(['web', 'auth'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('schedule')->name('schedule.')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('index');
    });
});
