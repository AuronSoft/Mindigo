<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherLiveSession\Http\Controllers\AdminLiveProviderHealthController;
use Mindigo\TeacherLiveSession\Http\Controllers\LiveProviderConnectionController;
use Mindigo\TeacherLiveSession\Http\Controllers\LiveSessionAttendanceReportController;
use Mindigo\TeacherLiveSession\Http\Controllers\LiveSessionBreakoutController;
use Mindigo\TeacherLiveSession\Http\Controllers\LiveSessionCollaborationController;
use Mindigo\TeacherLiveSession\Http\Controllers\LiveSessionMediaController;
use Mindigo\TeacherLiveSession\Http\Controllers\LiveSessionRecordingConsentController;
use Mindigo\TeacherLiveSession\Http\Controllers\LiveSessionRecordingController;
use Mindigo\TeacherLiveSession\Http\Controllers\LiveSessionReportController;
use Mindigo\TeacherLiveSession\Http\Controllers\LiveSessionTeachingToolController;
use Mindigo\TeacherLiveSession\Http\Controllers\PublicLiveSessionGuestController;
use Mindigo\TeacherLiveSession\Http\Controllers\PublicLiveSessionGuestMediaController;
use Mindigo\TeacherLiveSession\Http\Controllers\TeacherLiveSessionController;

Route::middleware(['web', 'auth', 'role:admin'])->prefix('admin/live-providers')->name('admin.live-providers.')->group(function () {
    Route::get('/health', [AdminLiveProviderHealthController::class, 'index'])->name('health');
    Route::get('/configuration', [AdminLiveProviderHealthController::class, 'configuration'])->name('configuration');
    Route::put('/configuration', [AdminLiveProviderHealthController::class, 'updateConfiguration'])->name('configuration.update');
    Route::post('/health/{provider}/reset', [AdminLiveProviderHealthController::class, 'reset'])->middleware('throttle:10,1')->name('health.reset');
});

Route::middleware(['web', 'auth', 'role:teacher|admin'])->prefix('teacher/live-providers')->name('teacher.live-providers.')->group(function () {
    Route::get('/{provider}/connect', [LiveProviderConnectionController::class, 'connect'])->middleware('throttle:10,1')->name('connect');
    Route::get('/{provider}/callback', [LiveProviderConnectionController::class, 'callback'])->middleware('throttle:10,1')->name('callback');
    Route::delete('/{provider}', [LiveProviderConnectionController::class, 'destroy'])->name('destroy');
});

Route::middleware(['web', 'auth', 'role:teacher|admin'])->prefix('teacher/live-sessions')->name('teacher.live-sessions.')->group(function () {
    Route::get('/', [TeacherLiveSessionController::class, 'index'])->name('index');
    Route::get('/create', [TeacherLiveSessionController::class, 'create'])->name('create');
    Route::post('/', [TeacherLiveSessionController::class, 'store'])->middleware('throttle:10,1')->name('store');
    Route::get('/reports', [LiveSessionReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [LiveSessionReportController::class, 'export'])->middleware('throttle:10,1')->name('reports.export');
    Route::get('/{liveSession}/attendance', [LiveSessionAttendanceReportController::class, 'show'])->name('attendance.show');
    Route::get('/{liveSession}/attendance/export', [LiveSessionAttendanceReportController::class, 'export'])->middleware('throttle:10,1')->name('attendance.export');

    Route::get('/{liveSession}/edit', [TeacherLiveSessionController::class, 'edit'])->name('edit');
    Route::put('/{liveSession}', [TeacherLiveSessionController::class, 'update'])->name('update');
    Route::delete('/{liveSession}', [TeacherLiveSessionController::class, 'destroy'])->name('destroy');
    Route::post('/{liveSession}/fallback-native', [TeacherLiveSessionController::class, 'fallbackToNative'])->middleware('throttle:5,1')->name('fallback-native');

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
    Route::post('/gateway-ticket', [PublicLiveSessionGuestMediaController::class, 'gatewayTicket'])->middleware('throttle:30,1')->name('gateway-ticket');
    Route::post('/presence', [PublicLiveSessionGuestMediaController::class, 'presence'])->name('presence');
    Route::post('/signals', [PublicLiveSessionGuestMediaController::class, 'signal'])->name('signals.store');
    Route::post('/signals/inbox', [PublicLiveSessionGuestMediaController::class, 'inbox'])->name('signals.inbox');
});

Route::middleware(['web', 'throttle:30,1'])->prefix('live/guest')->name('live-guest.')->group(function () {
    Route::get('/{token}', [PublicLiveSessionGuestController::class, 'show'])->where('token', '[A-Za-z0-9]{64}')->name('show');
    Route::post('/{token}', [PublicLiveSessionGuestController::class, 'join'])->where('token', '[A-Za-z0-9]{64}')->middleware('throttle:10,1')->name('join');
    Route::get('/status/{guest}', [PublicLiveSessionGuestController::class, 'status'])->whereNumber('guest')->name('status');
    Route::post('/status/{guest}/recording-consent', [PublicLiveSessionGuestController::class, 'consent'])->whereNumber('guest')->name('consent');
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('live-breakouts/{liveSession}')->name('live-breakouts.')->group(function () {
        Route::post('/sync', [LiveSessionBreakoutController::class, 'sync'])->middleware('throttle:120,1')->name('sync');
        Route::post('/', [LiveSessionBreakoutController::class, 'store'])->middleware('throttle:10,1')->name('store');
        Route::post('/open', [LiveSessionBreakoutController::class, 'open'])->middleware('throttle:10,1')->name('open');
        Route::post('/close', [LiveSessionBreakoutController::class, 'close'])->middleware('throttle:10,1')->name('close');
        Route::post('/main', [LiveSessionBreakoutController::class, 'returnToMain'])->middleware('throttle:30,1')->name('main');
        Route::post('/{room}/assign', [LiveSessionBreakoutController::class, 'assign'])->middleware('throttle:30,1')->name('assign');
        Route::post('/{room}/visit', [LiveSessionBreakoutController::class, 'visit'])->middleware('throttle:30,1')->name('visit');
    });
    Route::post('/live-recording-consent/{liveSession}', [LiveSessionRecordingConsentController::class, 'store'])->name('live-recording-consent.store');
    Route::post('/live-recordings/{liveSession}', [LiveSessionRecordingController::class, 'start'])->middleware('throttle:10,1')->name('live-recordings.start');
    Route::post('/live-recordings/{liveSession}/{recording}/chunks', [LiveSessionRecordingController::class, 'chunk'])->middleware('throttle:120,1')->name('live-recordings.chunk');
    Route::post('/live-recordings/{liveSession}/{recording}/finalize', [LiveSessionRecordingController::class, 'finalize'])->middleware('throttle:10,1')->name('live-recordings.finalize');
    Route::post('/live-recordings/{liveSession}/{recording}/abort', [LiveSessionRecordingController::class, 'abort'])->middleware('throttle:10,1')->name('live-recordings.abort');
    Route::get('/live-recordings/play/{recording}', [LiveSessionRecordingController::class, 'stream'])->middleware('throttle:120,1')->name('live-recordings.stream');
    Route::prefix('live-teaching-tools/{liveSession}')->name('live-teaching-tools.')->group(function () {
        Route::post('/sync', [LiveSessionTeachingToolController::class, 'sync'])->middleware('throttle:120,1')->name('sync');
        Route::post('/whiteboard', [LiveSessionTeachingToolController::class, 'whiteboard'])->middleware('throttle:120,1')->name('whiteboard');
        Route::post('/polls', [LiveSessionTeachingToolController::class, 'createPoll'])->middleware('throttle:20,1')->name('polls.store');
        Route::post('/polls/{poll}/vote', [LiveSessionTeachingToolController::class, 'vote'])->middleware('throttle:20,1')->name('polls.vote');
        Route::post('/polls/{poll}/close', [LiveSessionTeachingToolController::class, 'closePoll'])->middleware('throttle:20,1')->name('polls.close');
        Route::post('/resources', [LiveSessionTeachingToolController::class, 'upload'])->middleware('throttle:20,1')->name('resources.store');
    });
    Route::get('/live-teaching-tools/resources/{resource}', [LiveSessionTeachingToolController::class, 'download'])->middleware('throttle:120,1')->name('live-teaching-tools.resources.download');
});

Route::middleware(['web', 'auth', 'throttle:120,1'])->prefix('live-collaboration/{liveSession}')->name('live-collaboration.')->group(function () {
    Route::post('/sync', [LiveSessionCollaborationController::class, 'sync'])->name('sync');
    Route::post('/messages', [LiveSessionCollaborationController::class, 'message'])->middleware('throttle:30,1')->name('messages.store');
    Route::post('/actions', [LiveSessionCollaborationController::class, 'action'])->middleware('throttle:30,1')->name('actions.store');
    Route::post('/moderate', [LiveSessionCollaborationController::class, 'moderate'])->middleware('throttle:30,1')->name('moderate');
});

Route::middleware(['web', 'auth', 'throttle:120,1'])->prefix('live-media/{liveSession}')->name('live-media.')->group(function () {
    Route::post('/gateway-ticket', [LiveSessionMediaController::class, 'gatewayTicket'])->middleware('throttle:30,1')->name('gateway-ticket');
    Route::post('/presence', [LiveSessionMediaController::class, 'presence'])->name('presence');
    Route::post('/signals', [LiveSessionMediaController::class, 'signal'])->name('signals.store');
    Route::post('/signals/inbox', [LiveSessionMediaController::class, 'inbox'])->name('signals.inbox');
    Route::post('/leave', [LiveSessionMediaController::class, 'leave'])->middleware('throttle:30,1')->name('leave');
});
