<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentExam\Http\Controllers\ExamController;

Route::prefix('student/exams')
    ->middleware(['web', 'auth'])
    ->name('student.exams.')
    ->group(function () {
        Route::get('/',                           [ExamController::class, 'index'])->name('index');
        Route::post('/{exam}/start',              [ExamController::class, 'start'])->name('start');
        Route::get('/attempts/{attempt}',         [ExamController::class, 'take'])->name('take');
        Route::post('/attempts/{attempt}/submit', [ExamController::class, 'submit'])->name('submit');
        Route::get('/attempts/{attempt}/result',  [ExamController::class, 'result'])->name('result');

        Route::post('/attempts/{attempt}/autosave', [ExamController::class, 'autosave'])->name('autosave');
        Route::post('/attempts/{attempt}/heartbeat', [ExamController::class, 'heartbeat'])->name('heartbeat');
    });
