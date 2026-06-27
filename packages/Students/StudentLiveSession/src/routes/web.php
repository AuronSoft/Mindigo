<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentLiveSession\Http\Controllers\LiveSessionController;

Route::middleware(['web', 'auth'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('live-sessions')->name('live-sessions.')->group(function () {
        Route::get('/', [LiveSessionController::class, 'index'])->name('index');
    });
});
