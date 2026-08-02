<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherQuestion\Http\Controllers\TeacherQuestionController;

Route::middleware(['web', 'auth', 'role:teacher|admin'])
    ->prefix('teacher/questions')
    ->name('teacher.questions.')
    ->group(function () {
        Route::get('/', [TeacherQuestionController::class, 'index'])->name('index');
        Route::get('/create', [TeacherQuestionController::class, 'create'])->name('create');
        Route::post('/', [TeacherQuestionController::class, 'store'])->name('store');
        Route::get('/{question}', [TeacherQuestionController::class, 'show'])->name('show');
        Route::get('/{question}/edit', [TeacherQuestionController::class, 'edit'])->name('edit');
        Route::put('/{question}', [TeacherQuestionController::class, 'update'])->name('update');
        Route::post('/{question}/submit', [TeacherQuestionController::class, 'submit'])->name('submit');
        Route::delete('/{question}', [TeacherQuestionController::class, 'destroy'])->name('destroy');
        // chỉnh
        Route::patch('/bulk/difficulty', [TeacherQuestionController::class, 'bulkDifficulty'])->name('bulk.difficulty');
        Route::patch('/bulk/status', [TeacherQuestionController::class, 'bulkStatus'])->name('bulk.status');
        Route::delete('/bulk/destroy', [TeacherQuestionController::class, 'bulkDestroy'])->name('bulk.destroy');

        Route::get('/do/import', [TeacherQuestionController::class, 'importForm'])->name('import');
        Route::post('/do/import', [TeacherQuestionController::class, 'importStore'])->name('import.store');
    });
