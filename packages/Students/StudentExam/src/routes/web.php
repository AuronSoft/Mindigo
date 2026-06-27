<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentExam\Http\Controllers\ExamController;

Route::middleware(['web', 'auth'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('exams')->name('exams.')->group(function () {
        Route::get('/', [ExamController::class, 'index'])->name('index');
    });
});
