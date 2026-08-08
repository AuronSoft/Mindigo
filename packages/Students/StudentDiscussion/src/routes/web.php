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
        Route::patch('/{thread}/messages/{message}', [DiscussionController::class, 'updateMessage'])->name('messages.update');
        Route::delete('/{thread}/messages/{message}', [DiscussionController::class, 'deleteMessage'])->name('messages.destroy');
        Route::post('/{thread}/messages/{message}/react', [DiscussionController::class, 'reactToMessage'])->name('messages.react');
        Route::post('/{thread}/typing', [DiscussionController::class, 'typing'])->name('typing');
        Route::get('/{thread}/messages/older', [DiscussionController::class, 'olderMessages'])->name('messages.older');
        Route::post('/{thread}/members', [DiscussionController::class, 'addMember'])->name('members.store');
        Route::delete('/{thread}/members/{user}', [DiscussionController::class, 'removeMember'])->name('members.destroy');
        Route::patch('/{thread}/members/{user}/role', [DiscussionController::class, 'updateMemberRole'])->name('members.role');
        Route::get('/attachments/{attachment}', [DiscussionController::class, 'attachment'])->name('attachments.show');
    });
});
