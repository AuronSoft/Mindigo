<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherAnnouncement\Http\Controllers\TeacherAnnouncementController;

Route::middleware(['web', 'auth', 'role:teacher|admin'])
    ->prefix('teacher/announcements')
    ->name('teacher.announcements.')
    ->group(function () {
        Route::get('/', [TeacherAnnouncementController::class, 'index'])->name('index');
        Route::get('/create', [TeacherAnnouncementController::class, 'create'])->name('create');
        Route::post('/', [TeacherAnnouncementController::class, 'store'])->name('store');
        Route::get('/{announcement}', [TeacherAnnouncementController::class, 'show'])->name('show');
        Route::get('/{announcement}/edit', [TeacherAnnouncementController::class, 'edit'])->name('edit');
        Route::put('/{announcement}', [TeacherAnnouncementController::class, 'update'])->name('update');
        Route::post('/{announcement}/publish', [TeacherAnnouncementController::class, 'publish'])->name('publish');
        Route::delete('/{announcement}', [TeacherAnnouncementController::class, 'destroy'])->name('destroy');
    });
