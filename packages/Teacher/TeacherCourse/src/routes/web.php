<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherCourse\Http\Controllers\ChapterController;
use Mindigo\TeacherCourse\Http\Controllers\CourseBuilderController;
use Mindigo\TeacherCourse\Http\Controllers\CourseController;
use Mindigo\TeacherCourse\Http\Controllers\CourseEnrollmentController;
use Mindigo\TeacherCourse\Http\Controllers\CourseLessonController;
use Mindigo\TeacherCourse\Http\Controllers\CourseMonitoringController;
use Mindigo\TeacherCourse\Http\Controllers\CoursePublicationController;
use Mindigo\TeacherCourse\Http\Controllers\LessonController;
use Mindigo\TeacherCourse\Http\Controllers\PublicCourseController;
use Mindigo\TeacherCourse\Http\Controllers\StudentCourseController;

Route::middleware('web')->group(function (): void {
    Route::get('/courses', [PublicCourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}', [PublicCourseController::class, 'show'])
        ->middleware('auth')
        ->name('courses.show');
    Route::post('/courses/{course}/enroll', [CourseEnrollmentController::class, 'store'])
        ->middleware(['auth', 'role:student'])
        ->name('courses.enroll');
    Route::middleware('auth')->prefix('/courses/{course}/lessons/{lesson}')->name('courses.lessons.')->group(function (): void {
        Route::get('/', [CourseLessonController::class, 'show'])->name('show');
        Route::get('/video', [CourseLessonController::class, 'video'])->name('video');
        Route::get('/attachments/{attachment}', [CourseLessonController::class, 'attachment'])->name('attachments.show');
    });
});

Route::middleware(['web', 'auth', 'role:student'])
    ->prefix('student/courses')
    ->name('student.courses.')
    ->group(function (): void {
        Route::get('/', [StudentCourseController::class, 'index'])->name('index');
        Route::get('/{course}', [StudentCourseController::class, 'show'])->name('show');
        Route::get('/{course}/lessons/{lesson}', [StudentCourseController::class, 'lesson'])->name('lessons.show');
        Route::post('/{course}/lessons/{lesson}/activity', [StudentCourseController::class, 'activity'])->name('lessons.activity');
        Route::post('/{course}/lessons/{lesson}/complete', [StudentCourseController::class, 'complete'])->name('lessons.complete');
    });

Route::middleware(['web', 'auth', 'role:teacher|admin'])
    ->prefix('teacher/courses')
    ->name('teacher.courses.')
    ->scopeBindings()
    ->group(function () {

        // Courses
        Route::get('/', [CourseController::class, 'index'])->name('index');
        Route::get('/create', [CourseController::class, 'create'])->name('create');
        Route::post('/', [CourseController::class, 'store'])->name('store');
        Route::get('/{course}', [CourseController::class, 'show'])->name('show');
        Route::get('/{course}/edit', [CourseController::class, 'edit'])->name('edit');
        Route::put('/{course}', [CourseController::class, 'update'])->name('update');
        Route::delete('/{course}', [CourseController::class, 'destroy'])->name('destroy');
        Route::patch('/{course}/publication', [CoursePublicationController::class, 'update'])->name('publication.update');
        Route::post('/{course}/assign', [CourseEnrollmentController::class, 'assign'])->name('assign');
        Route::post('/{course}/duplicate', [CourseBuilderController::class, 'duplicate'])->name('duplicate');
        Route::patch('/{course}/curriculum-order', [CourseBuilderController::class, 'reorder'])->name('curriculum.reorder');
        Route::get('/{course}/monitor', CourseMonitoringController::class)->name('monitor');

        // Chapters
        Route::post('/{course}/chapters', [ChapterController::class, 'store'])->name('chapters.store');
        Route::put('/{course}/chapters/{chapter}', [ChapterController::class, 'update'])->name('chapters.update');
        Route::delete('/{course}/chapters/{chapter}', [ChapterController::class, 'destroy'])->name('chapters.destroy');

        // Lessons
        Route::get('/{course}/chapters/{chapter}/lessons/create', [LessonController::class, 'create'])->name('lessons.create');
        Route::post('/{course}/chapters/{chapter}/lessons', [LessonController::class, 'store'])->name('lessons.store');
        Route::get('/lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('lessons.edit');
        Route::post('/lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
        Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');
    });
