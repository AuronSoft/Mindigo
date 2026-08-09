<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentClassroom\Http\Controllers\ClassroomController;

Route::middleware(['web', 'auth', 'role:student|admin'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('classrooms')->name('classrooms.')->group(function () {
        Route::get('/', [ClassroomController::class, 'index'])->name('index');
        Route::get('/{classroom}', [ClassroomController::class, 'show'])->name('show');
        Route::get('/{classroom}/announcements/{announcement}', [ClassroomController::class, 'announcement'])->name('announcements.show');
    });
});
