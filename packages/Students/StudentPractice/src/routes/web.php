<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentPractice\Http\Controllers\PracticeController;

Route::middleware(['web', 'auth', 'role:student|admin'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('practice')->name('practice.')->group(function () {
        Route::get('/', [PracticeController::class, 'index'])->name('index');
        Route::get('/history', [PracticeController::class, 'history'])->name('history');
        Route::post('/start', [PracticeController::class, 'start'])->name('start');
        Route::get('/questions/{question}', [PracticeController::class, 'show'])->name('show')->whereNumber('question');
        Route::get('/{attempt}/attempt', [PracticeController::class, 'attempt'])->name('attempt')->whereNumber('attempt');
        Route::post('/{attempt}/submit-answer', [PracticeController::class, 'submitAnswer'])->name('submit-answer')->whereNumber('attempt');
        Route::post('/{attempt}/complete', [PracticeController::class, 'complete'])->name('complete')->whereNumber('attempt');
        Route::get('/{attempt}/result', [PracticeController::class, 'result'])->name('result')->whereNumber('attempt');
    });
});
