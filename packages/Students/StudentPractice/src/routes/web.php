<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentPractice\Http\Controllers\PracticeController;

Route::middleware(['web', 'auth', 'role:student|admin'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('practice')->name('practice.')->group(function () {
        Route::get('/', [PracticeController::class, 'index'])->name('index');
    });
});
