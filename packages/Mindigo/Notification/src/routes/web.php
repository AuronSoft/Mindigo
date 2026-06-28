<?php

use Illuminate\Support\Facades\Route;
use Mindigo\Notification\Http\Controllers\NotificationController;

Route::middleware(['web', 'auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/read-all', [NotificationController::class, 'readAll'])->name('read-all');
    Route::get('/{id}/read', [NotificationController::class, 'read'])->name('read');
});
