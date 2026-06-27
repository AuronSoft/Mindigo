<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentLeaderboard\Http\Controllers\LeaderboardController;

Route::middleware(['web', 'auth'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('leaderboard')->name('leaderboard.')->group(function () {
        Route::get('/', [LeaderboardController::class, 'index'])->name('index');
    });
});
