<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherOnboarding\Http\Controllers\AdminTeacherApplicationController;
use Mindigo\TeacherOnboarding\Http\Controllers\TeacherApplicationController;

Route::middleware('web')->group(function (): void {
    Route::get('/become-teacher', [TeacherApplicationController::class, 'create'])->name('teacher-applications.create');
    Route::post('/become-teacher', [TeacherApplicationController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('teacher-applications.store');
});

Route::middleware(['web', 'auth'])
    ->prefix('admin/teacher-applications')
    ->name('admin.teacher-applications.')
    ->group(function (): void {
        Route::get('/', [AdminTeacherApplicationController::class, 'index'])->name('index');
        Route::get('/{teacher_application}', [AdminTeacherApplicationController::class, 'show'])->name('show');
        Route::patch('/{teacher_application}', [AdminTeacherApplicationController::class, 'update'])->name('update');
        Route::get('/{teacher_application}/documents/{document}', [AdminTeacherApplicationController::class, 'document'])->name('documents.show');
    });
