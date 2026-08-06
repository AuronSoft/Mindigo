<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherOnboarding\Http\Controllers\TeacherApplicationController;

Route::middleware('web')->group(function (): void {
    Route::get('/become-teacher', [TeacherApplicationController::class, 'create'])->name('teacher-applications.create');
    Route::post('/become-teacher', [TeacherApplicationController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('teacher-applications.store');
});
