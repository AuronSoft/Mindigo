<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherDiscussion\Http\Controllers\TeacherDiscussionController;

Route::middleware(['web', 'auth', 'role:teacher|admin'])
    ->prefix('teacher/discussions')
    ->name('teacher.discussions.')
    ->group(function () {
        Route::get('/', [TeacherDiscussionController::class, 'index'])->name('index');
        Route::post('/groups', [TeacherDiscussionController::class, 'createGroup'])->name('groups.store');
        Route::post('/direct', [TeacherDiscussionController::class, 'findOrCreateDirect'])->name('direct.store');
        Route::post('/{thread}/messages', [TeacherDiscussionController::class, 'store'])->name('messages.store');
        Route::post('/{thread}/mark-read', [TeacherDiscussionController::class, 'markAsRead'])->name('mark-read');
        Route::post('/{thread}/members', [TeacherDiscussionController::class, 'addMember'])->name('members.store');
        Route::delete('/{thread}/members/{user}', [TeacherDiscussionController::class, 'removeMember'])->name('members.destroy');
        Route::get('/attachments/{attachment}', [TeacherDiscussionController::class, 'attachment'])->name('attachments.show');
    });
