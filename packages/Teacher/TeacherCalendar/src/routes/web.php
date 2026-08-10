<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherCalendar\Http\Controllers\TeacherCalendarController;

Route::middleware(['web', 'auth', 'role:teacher'])->prefix('teacher/calendar')->name('teacher.calendar.')->group(function (): void {
    Route::get('/', [TeacherCalendarController::class, 'index'])->name('index');
    Route::post('/classrooms/{classroom}/sessions', [TeacherCalendarController::class, 'store'])->name('sessions.store');
    Route::post('/sessions/{schedule}/cancel', [TeacherCalendarController::class, 'cancel'])->name('sessions.cancel');
});
