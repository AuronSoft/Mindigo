<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherOnboarding\Http\Controllers\AdminTeacherApplicationController;
use Mindigo\TeacherOnboarding\Http\Controllers\TeacherApplicationController;
use Mindigo\TeacherOnboarding\Http\Controllers\TeacherApplicationInterviewController;

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
        Route::post('/{teacher_application}/interviews', [TeacherApplicationInterviewController::class, 'store'])->name('interviews.store');
        Route::get('/{teacher_application}/interviews/{interview}', [TeacherApplicationInterviewController::class, 'show'])->name('interviews.show');
        Route::patch('/{teacher_application}/interviews/{interview}', [TeacherApplicationInterviewController::class, 'update'])->name('interviews.update');
        Route::patch('/{teacher_application}/interviews/{interview}/evaluation', [TeacherApplicationInterviewController::class, 'evaluate'])->name('interviews.evaluate');
    });
