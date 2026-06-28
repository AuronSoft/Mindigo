<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentDiscussion\Http\Controllers\DiscussionController;

Route::middleware(['web', 'auth', 'role:student|admin'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('discussions')->name('discussions.')->group(function () {
        Route::get('/', [DiscussionController::class, 'index'])->name('index');
        Route::post('/{thread}/messages', [DiscussionController::class, 'store'])->name('messages.store');
        Route::get('/attachments/{attachment}', [DiscussionController::class, 'attachment'])->name('attachments.show');
    });
});
