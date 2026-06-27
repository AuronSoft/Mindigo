<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentProgress\Http\Controllers\ProgressController;

Route::middleware(['web', 'auth', 'role:student|admin'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('progress')->name('progress.')->group(function () {
        Route::get('/', [ProgressController::class, 'index'])->name('index');
    });
});
