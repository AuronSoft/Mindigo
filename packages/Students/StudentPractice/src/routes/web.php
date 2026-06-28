<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentPractice\Http\Controllers\PracticeController;

Route::middleware(['web', 'auth', 'role:student|admin'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('practice')->name('practice.')->group(function () {
        Route::get('/', [PracticeController::class, 'index'])->name('index');
        Route::get('/history', [PracticeController::class, 'history'])->name('history');
        Route::post('/start', [PracticeController::class, 'start'])->name('start');
        Route::get('/{id}', [PracticeController::class, 'show'])->name('show')->whereNumber('id');
        Route::get('/{id}/attempt', [PracticeController::class, 'attempt'])->name('attempt')->whereNumber('id');
        Route::post('/{id}/submit-answer', [PracticeController::class, 'submitAnswer'])->name('submit-answer')->whereNumber('id');
        Route::post('/{id}/complete', [PracticeController::class, 'complete'])->name('complete')->whereNumber('id');
        Route::get('/{id}/result', [PracticeController::class, 'result'])->name('result')->whereNumber('id');
    });
});
