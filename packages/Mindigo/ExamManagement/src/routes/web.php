<?php

use Illuminate\Support\Facades\Route;
use Mindigo\ExamManagement\Http\Controllers\ExamAttemptController;
use Mindigo\ExamManagement\Http\Controllers\ExamController;
use Mindigo\ExamManagement\Http\Middleware\EnsureExamBusinessRole;

Route::middleware([
    'web',
    'auth',
])
    ->prefix('dashboard/exams')
    ->name('exams.')
    ->group(function () {
        Route::get('/', [ExamController::class, 'index'])->name('index');

        Route::middleware(EnsureExamBusinessRole::class)->group(function () {
            Route::get('/create', [ExamController::class, 'create'])->name('create');
            Route::post('/', [ExamController::class, 'store'])->name('store');
            Route::get('/attempts/{attempt}', [ExamAttemptController::class, 'take'])->name('attempts.take');
            Route::post('/attempts/{attempt}/autosave', [ExamAttemptController::class, 'autosave'])->name('attempts.autosave');
            Route::post('/attempts/{attempt}/submit', [ExamAttemptController::class, 'submit'])->name('attempts.submit');
            Route::post('/attempts/{attempt}/violation', [ExamAttemptController::class, 'logViolation'])->name('attempts.violation');
            Route::get('/attempts/{attempt}/result', [ExamAttemptController::class, 'result'])->name('attempts.result');
            Route::get('/{exam}', [ExamController::class, 'show'])->name('show');
            Route::get('/{exam}/edit', [ExamController::class, 'edit'])->name('edit');
            Route::put('/{exam}', [ExamController::class, 'update'])->name('update');
            Route::post('/{exam}/publish', [ExamController::class, 'publish'])->name('publish');
            Route::post('/{exam}/close', [ExamController::class, 'close'])->name('close');
            Route::post('/{exam}/start', [ExamAttemptController::class, 'start'])->name('start');
            Route::delete('/{exam}', [ExamController::class, 'destroy'])->name('destroy');
        });
    });
