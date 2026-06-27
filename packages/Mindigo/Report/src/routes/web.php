<?php

use Illuminate\Support\Facades\Route;
use Mindigo\Report\Http\Controllers\ReportController;

Route::middleware(['web', 'auth', 'role:admin'])
    ->prefix('reports')
    ->name('reports.')
    ->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/exams', [ReportController::class, 'exams'])->name('exams');
        Route::get('/exams/{exam}', [ReportController::class, 'examDetail'])->name('exam.detail');
        Route::get('/students', [ReportController::class, 'students'])->name('students');
        Route::get('/students/{user}', [ReportController::class, 'studentDetail'])->name('student.detail');
    });

Route::middleware(['web', 'auth', 'role:teacher|admin'])
    ->prefix('teacher/reports')
    ->name('teacher.reports.')
    ->group(function () {
        Route::get('/', [ReportController::class, 'teacher'])->name('index');
    });
