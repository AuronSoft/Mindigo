<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherClassroom\Http\Controllers\TeacherClassroomController;

Route::middleware(['web', 'auth', 'role:teacher|admin'])
    ->prefix('teacher/classrooms')
    ->name('teacher.classrooms.')
    ->group(function () {
        Route::get('/', [TeacherClassroomController::class, 'index'])->name('index');
        Route::get('/create', [TeacherClassroomController::class, 'create'])->name('create');
        Route::post('/', [TeacherClassroomController::class, 'store'])->name('store');
        Route::get('/{classroom}', [TeacherClassroomController::class, 'show'])->name('show');
        Route::get('/{classroom}/edit', [TeacherClassroomController::class, 'edit'])->name('edit');
        Route::put('/{classroom}', [TeacherClassroomController::class, 'update'])->name('update');
        Route::post('/{classroom}/students', [TeacherClassroomController::class, 'syncStudents'])->name('students.sync');
        Route::delete('/{classroom}', [TeacherClassroomController::class, 'destroy'])->name('destroy');

        // Attendance routes
        Route::get('/{classroom}/attendance', [TeacherClassroomController::class, 'getAttendance'])->name('attendance.index');
        Route::post('/{classroom}/attendance', [TeacherClassroomController::class, 'saveAttendance'])->name('attendance.save');

        // Schedule routes
        Route::post('/{classroom}/schedules', [TeacherClassroomController::class, 'storeSchedule'])->name('schedules.store');
        Route::put('/schedules/{schedule}', [TeacherClassroomController::class, 'updateSchedule'])->name('schedules.update');
        Route::delete('/schedules/{schedule}', [TeacherClassroomController::class, 'destroySchedule'])->name('schedules.destroy');
    });
