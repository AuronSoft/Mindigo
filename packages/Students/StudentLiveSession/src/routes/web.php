<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentLiveSession\Http\Controllers\LiveSessionController;

Route::middleware(['web', 'auth', 'role:student|admin'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('live-sessions')->name('live-sessions.')->group(function () {
        Route::get('/', [LiveSessionController::class, 'index'])->name('index');
        Route::get('/{liveSession}/room', [LiveSessionController::class, 'room'])->middleware('throttle:30,1')->name('room');
        Route::post('/{liveSession}/join-token', [LiveSessionController::class, 'joinToken'])->middleware('throttle:20,1')->name('join-token');
    });
});
