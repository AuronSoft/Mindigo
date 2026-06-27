<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherDiscussion\Http\Controllers\TeacherDiscussionController;

Route::middleware(['web', 'auth', 'role:teacher|admin'])
    ->prefix('teacher/discussions')
    ->name('teacher.discussions.')
    ->group(function () {
        Route::get('/', [TeacherDiscussionController::class, 'index'])->name('index');
        Route::post('/{thread}/messages', [TeacherDiscussionController::class, 'store'])->name('messages.store');
        Route::get('/attachments/{attachment}', [TeacherDiscussionController::class, 'attachment'])->name('attachments.show');
    });
