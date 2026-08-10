<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherClassroom\Http\Controllers\ClassroomAttendanceController;
use Mindigo\TeacherClassroom\Http\Controllers\ClassroomScheduleController;
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
        Route::get('/{classroom}/attendance', [ClassroomAttendanceController::class, 'getAttendance'])->name('attendance.index');
        Route::post('/{classroom}/attendance', [ClassroomAttendanceController::class, 'saveAttendance'])->name('attendance.save');
        Route::post('/{classroom}/attendance/code', [ClassroomAttendanceController::class, 'openCodeSession'])->name('attendance.code.open');
        Route::delete('/attendance/code/{attendanceSession}', [ClassroomAttendanceController::class, 'closeCodeSession'])->name('attendance.code.close');

        // Schedule routes
        Route::post('/{classroom}/schedules', [ClassroomScheduleController::class, 'storeSchedule'])->name('schedules.store');
        Route::post('/{classroom}/schedules/generate-course-plan', [ClassroomScheduleController::class, 'generateCoursePlan'])->name('schedules.generate-course-plan');
        Route::put('/schedules/{schedule}', [ClassroomScheduleController::class, 'updateSchedule'])->name('schedules.update');
        Route::delete('/schedules/{schedule}', [ClassroomScheduleController::class, 'destroySchedule'])->name('schedules.destroy');
    });
