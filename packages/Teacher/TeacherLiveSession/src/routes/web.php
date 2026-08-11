<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherLiveSession\Http\Controllers\LiveSessionCollaborationController;
use Mindigo\TeacherLiveSession\Http\Controllers\LiveSessionMediaController;
use Mindigo\TeacherLiveSession\Http\Controllers\PublicLiveSessionGuestController;
use Mindigo\TeacherLiveSession\Http\Controllers\PublicLiveSessionGuestMediaController;
use Mindigo\TeacherLiveSession\Http\Controllers\TeacherLiveSessionController;

Route::middleware(['web', 'auth', 'role:teacher|admin'])->prefix('teacher/live-sessions')->name('teacher.live-sessions.')->group(function () {
    Route::get('/', [TeacherLiveSessionController::class, 'index'])->name('index');
    Route::get('/create', [TeacherLiveSessionController::class, 'create'])->name('create');
    Route::post('/', [TeacherLiveSessionController::class, 'store'])->middleware('throttle:10,1')->name('store');

    Route::get('/{liveSession}/edit', [TeacherLiveSessionController::class, 'edit'])->name('edit');
    Route::put('/{liveSession}', [TeacherLiveSessionController::class, 'update'])->name('update');
    Route::delete('/{liveSession}', [TeacherLiveSessionController::class, 'destroy'])->name('destroy');

    Route::post('/{liveSession}/start', [TeacherLiveSessionController::class, 'start'])->middleware('throttle:20,1')->name('start');
    Route::post('/{liveSession}/open', [TeacherLiveSessionController::class, 'openWaitingRoom'])->middleware('throttle:10,1')->name('open');
    Route::get('/{liveSession}/room', [TeacherLiveSessionController::class, 'room'])->middleware('throttle:60,1')->name('room');
    Route::post('/{liveSession}/end', [TeacherLiveSessionController::class, 'end'])->middleware('throttle:20,1')->name('end');
    Route::post('/{liveSession}/cancel', [TeacherLiveSessionController::class, 'cancel'])->name('cancel-session');
    Route::post('/{liveSession}/lock', [TeacherLiveSessionController::class, 'lock'])->name('lock');
    Route::post('/{liveSession}/unlock', [TeacherLiveSessionController::class, 'unlock'])->name('unlock');
    Route::post('/{liveSession}/join-token', [TeacherLiveSessionController::class, 'joinToken'])->middleware('throttle:20,1')->name('join-token');
    Route::post('/{liveSession}/participants/{participant}/admit', [TeacherLiveSessionController::class, 'admit'])->middleware('throttle:30,1')->name('participants.admit');
    Route::post('/{liveSession}/participants/{participant}/deny', [TeacherLiveSessionController::class, 'deny'])->middleware('throttle:30,1')->name('participants.deny');
    Route::post('/{liveSession}/participants/{participant}/remove', [TeacherLiveSessionController::class, 'remove'])->middleware('throttle:30,1')->name('participants.remove');
    Route::post('/{liveSession}/guest-links', [TeacherLiveSessionController::class, 'createGuestLink'])->middleware('throttle:10,1')->name('guest-links.store');
    Route::delete('/{liveSession}/guest-links/{guestLink}', [TeacherLiveSessionController::class, 'revokeGuestLink'])->name('guest-links.destroy');
    Route::post('/{liveSession}/guests/{guest}/decision', [TeacherLiveSessionController::class, 'decideGuest'])->middleware('throttle:30,1')->name('guests.decision');
});

Route::middleware(['web', 'throttle:120,1'])->prefix('live/guest-media/{liveSession}/{guest}')->name('live-guest-media.')->scopeBindings()->group(function () {
    Route::post('/presence', [PublicLiveSessionGuestMediaController::class, 'presence'])->name('presence');
    Route::post('/signals', [PublicLiveSessionGuestMediaController::class, 'signal'])->name('signals.store');
    Route::post('/signals/inbox', [PublicLiveSessionGuestMediaController::class, 'inbox'])->name('signals.inbox');
});

Route::middleware(['web', 'throttle:30,1'])->prefix('live/guest')->name('live-guest.')->group(function () {
    Route::get('/{token}', [PublicLiveSessionGuestController::class, 'show'])->where('token', '[A-Za-z0-9]{64}')->name('show');
    Route::post('/{token}', [PublicLiveSessionGuestController::class, 'join'])->where('token', '[A-Za-z0-9]{64}')->middleware('throttle:10,1')->name('join');
    Route::get('/status/{guest}', [PublicLiveSessionGuestController::class, 'status'])->whereNumber('guest')->name('status');
});

Route::middleware(['web', 'auth', 'throttle:120,1'])->prefix('live-collaboration/{liveSession}')->name('live-collaboration.')->group(function () {
    Route::post('/sync', [LiveSessionCollaborationController::class, 'sync'])->name('sync');
    Route::post('/messages', [LiveSessionCollaborationController::class, 'message'])->middleware('throttle:30,1')->name('messages.store');
    Route::post('/actions', [LiveSessionCollaborationController::class, 'action'])->middleware('throttle:30,1')->name('actions.store');
    Route::post('/moderate', [LiveSessionCollaborationController::class, 'moderate'])->middleware('throttle:30,1')->name('moderate');
});

Route::middleware(['web', 'auth', 'throttle:120,1'])->prefix('live-media/{liveSession}')->name('live-media.')->group(function () {
    Route::post('/presence', [LiveSessionMediaController::class, 'presence'])->name('presence');
    Route::post('/signals', [LiveSessionMediaController::class, 'signal'])->name('signals.store');
    Route::post('/signals/inbox', [LiveSessionMediaController::class, 'inbox'])->name('signals.inbox');
});
