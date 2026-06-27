<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentLeaderboard\Http\Controllers\LeaderboardController;

Route::middleware(['web', 'auth', 'role:student|admin'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('leaderboard')->name('leaderboard.')->group(function () {
        Route::get('/', [LeaderboardController::class, 'index'])->name('index');
    });
});
