<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentExam\Http\Controllers\ExamController;
use Mindigo\StudentExam\Http\Controllers\SessionAttemptController;

Route::prefix('student/exam-sessions')
    ->middleware(['web', 'auth', 'role:student'])
    ->name('student.exam-sessions.')
    ->group(function (): void {
        Route::get('/', [SessionAttemptController::class, 'index'])->name('index');
        Route::post('/{session}/start', [SessionAttemptController::class, 'start'])->name('start');
        Route::get('/attempts/{attempt}', [SessionAttemptController::class, 'take'])->name('take');
        Route::post('/attempts/{attempt}/autosave', [SessionAttemptController::class, 'autosave'])->name('autosave');
        Route::post('/attempts/{attempt}/heartbeat', [SessionAttemptController::class, 'heartbeat'])->name('heartbeat');
        Route::post('/attempts/{attempt}/security-event', [SessionAttemptController::class, 'securityEvent'])->name('security-event');
        Route::post('/attempts/{attempt}/submit', [SessionAttemptController::class, 'submit'])->name('submit');
        Route::get('/attempts/{attempt}/result', [SessionAttemptController::class, 'result'])->name('result');
    });

Route::prefix('student/exams')
    ->middleware(['web', 'auth', 'role:student'])
    ->name('student.exams.')
    ->group(function () {
        Route::get('/', [ExamController::class, 'index'])->name('index');
        Route::post('/{exam}/start', [ExamController::class, 'start'])->name('start');
        Route::get('/attempts/{attempt}', [ExamController::class, 'take'])->name('take');
        Route::post('/attempts/{attempt}/submit', [ExamController::class, 'submit'])->name('submit');
        Route::get('/attempts/{attempt}/result', [ExamController::class, 'result'])->name('result');

        Route::post('/attempts/{attempt}/autosave', [ExamController::class, 'autosave'])->name('autosave');
        Route::post('/attempts/{attempt}/heartbeat', [ExamController::class, 'heartbeat'])->name('heartbeat');
    });
