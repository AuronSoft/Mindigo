<?php

use Illuminate\Support\Facades\Route;
use Mindigo\Auth\Http\Controllers\ForgotPasswordController;
use Mindigo\Auth\Http\Controllers\LoginController;
use Mindigo\Auth\Http\Controllers\MindigoIdController;

Route::middleware(['web', 'guest'])->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'index'])->name('password.request');
    Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp'])->name('password.send-otp');
    Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verify-otp');
    Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'reset'])->name('password.reset');

    // Mindigo ID (passwordless)
    Route::prefix('Mindigo-id')->name('Mindigo-id.')->group(function () {
        Route::post('send', [MindigoIdController::class, 'send'])->name('send')->middleware('throttle:Mindigo-id-send');
        Route::post('verify-otp', [MindigoIdController::class, 'verifyOtp'])->name('verify-otp')->middleware('throttle:Mindigo-id-otp');
    });
});

// Magic link verify — không cần guest (link mở trên trình duyệt bất kỳ)
Route::get('/Mindigo-id/verify', [MindigoIdController::class, 'verifyMagicLink'])
    ->name('Mindigo-id.verify')
    ->middleware('web');

Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware(['web', 'auth']);
