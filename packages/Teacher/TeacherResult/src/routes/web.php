<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherResult\Http\Controllers\TeacherResultController;

Route::middleware(['web', 'auth', 'role:teacher|admin'])
    ->prefix('teacher/results')
    ->name('teacher.results.')
    ->group(function () {
        Route::get('/', [TeacherResultController::class, 'index'])->name('index');
        Route::get('/exams/{exam}', [TeacherResultController::class, 'byExam'])->name('by_exam');
        Route::get('/students/{user}', [TeacherResultController::class, 'byStudent'])->name('by_student');
    });
