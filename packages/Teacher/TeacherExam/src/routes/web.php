<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherExam\Http\Controllers\TeacherExamController;

Route::middleware(['web', 'auth', 'role:teacher|admin'])
    ->prefix('teacher/exams')
    ->name('teacher.exams.')
    ->group(function () {
        Route::get('/', [TeacherExamController::class, 'index'])->name('index');
        Route::get('/create', [TeacherExamController::class, 'create'])->name('create');
        Route::post('/', [TeacherExamController::class, 'store'])->name('store');
        Route::get('/{exam}', [TeacherExamController::class, 'show'])->name('show');
        Route::get('/{exam}/edit', [TeacherExamController::class, 'edit'])->name('edit');
        Route::put('/{exam}', [TeacherExamController::class, 'update'])->name('update');
        Route::post('/{exam}/publish', [TeacherExamController::class, 'publish'])->name('publish');
        Route::post('/{exam}/close', [TeacherExamController::class, 'close'])->name('close');
        Route::delete('/{exam}', [TeacherExamController::class, 'destroy'])->name('destroy');
        Route::get('/{exam}/print', [TeacherExamController::class, 'print'])->name('print');
        Route::get('/{exam}/monitor', [TeacherExamController::class, 'monitor'])->name('monitor');
        Route::get('/{exam}/monitor/data', [TeacherExamController::class, 'monitorData'])->name('monitor.data');
        Route::get('/{exam}/attempts/{attempt}/grade', [TeacherExamController::class, 'grade'])->name('attempts.grade');
        Route::put('/{exam}/attempts/{attempt}/grade', [TeacherExamController::class, 'updateGrade'])->name('attempts.grade.update');
    });
