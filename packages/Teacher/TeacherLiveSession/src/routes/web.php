<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherLiveSession\Http\Controllers\TeacherLiveSessionController;

Route::middleware(['web', 'auth', 'role:teacher|admin'])->prefix('teacher/live-sessions')->name('teacher.live-sessions.')->group(function () {
    Route::get('/', [TeacherLiveSessionController::class, 'index'])->name('index');
    Route::get('/create', [TeacherLiveSessionController::class, 'create'])->name('create');
    Route::post('/', [TeacherLiveSessionController::class, 'store'])->name('store');

    Route::get('/{liveSession}/edit', [TeacherLiveSessionController::class, 'edit'])->name('edit');
    Route::put('/{liveSession}', [TeacherLiveSessionController::class, 'update'])->name('update');
    Route::delete('/{liveSession}', [TeacherLiveSessionController::class, 'destroy'])->name('destroy');

    Route::post('/{liveSession}/start', [TeacherLiveSessionController::class, 'start'])->name('start');
    Route::get('/{liveSession}/room', [TeacherLiveSessionController::class, 'room'])->name('room');
    Route::post('/{liveSession}/end', [TeacherLiveSessionController::class, 'end'])->name('end');
});
