<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherCourse\Http\Controllers\ChapterController;
use Mindigo\TeacherCourse\Http\Controllers\CourseController;
use Mindigo\TeacherCourse\Http\Controllers\CoursePublicationController;
use Mindigo\TeacherCourse\Http\Controllers\LessonController;

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
