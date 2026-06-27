<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentPractice\Http\Controllers\PracticeController;

Route::middleware(['web', 'auth'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('practice')->name('practice.')->group(function () {
        Route::get('/', [PracticeController::class, 'index'])->name('index');
    });
});
