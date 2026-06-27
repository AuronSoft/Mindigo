<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentHistory\Http\Controllers\HistoryController;

Route::middleware(['web', 'auth'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('history')->name('history.')->group(function () {
        Route::get('/', [HistoryController::class, 'index'])->name('index');
    });
});
