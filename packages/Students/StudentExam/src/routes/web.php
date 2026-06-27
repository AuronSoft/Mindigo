<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentExam\Http\Controllers\ExamController;

Route::middleware(['web', 'auth', 'role:student|admin'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('exams')->name('exams.')->group(function () {
        Route::get('/', [ExamController::class, 'index'])->name('index');
    });
});
