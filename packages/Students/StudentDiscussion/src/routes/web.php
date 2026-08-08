<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentDiscussion\Http\Controllers\DiscussionController;

Route::middleware(['web', 'auth', 'role:student|admin'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('discussions')->name('discussions.')->group(function () {
        Route::get('/', [DiscussionController::class, 'index'])->name('index');
        Route::post('/groups', [DiscussionController::class, 'createGroup'])->name('groups.store');
        Route::post('/direct', [DiscussionController::class, 'findOrCreateDirect'])->name('direct.store');
        Route::post('/{thread}/messages', [DiscussionController::class, 'store'])->name('messages.store');
        Route::post('/mark-all-read', [DiscussionController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::patch('/{thread}/preferences', [DiscussionController::class, 'updatePreferences'])->name('preferences.update');
        Route::patch('/{thread}/messages/{message}/pin', [DiscussionController::class, 'updateMessagePin'])->name('messages.pin');
        Route::post('/{thread}/members', [DiscussionController::class, 'addMember'])->name('members.store');
        Route::delete('/{thread}/members/{user}', [DiscussionController::class, 'removeMember'])->name('members.destroy');
        Route::get('/attachments/{attachment}', [DiscussionController::class, 'attachment'])->name('attachments.show');
    });
});
