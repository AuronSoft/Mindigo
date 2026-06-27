<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherAssignment\Http\Controllers\AssignmentController;
use Mindigo\TeacherAssignment\Http\Controllers\SubmissionController;

Route::middleware(['web', 'auth'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/', [AssignmentController::class, 'index'])->name('index');
        Route::get('/create', [AssignmentController::class, 'create'])->name('create');
        Route::post('/', [AssignmentController::class, 'store'])->name('store');
        Route::get('/{assignment}/files/{fileIndex}', [AssignmentController::class, 'file'])->whereNumber('fileIndex')->name('files.show');
        Route::get('/{assignment}/edit', [AssignmentController::class, 'edit'])->name('edit');
        Route::put('/{assignment}', [AssignmentController::class, 'update'])->name('update');
        Route::delete('/{assignment}', [AssignmentController::class, 'destroy'])->name('destroy');

        Route::prefix('{assignment}/submissions')->name('submissions.')->group(function () {
            Route::get('/', [SubmissionController::class, 'index'])->name('index');
            Route::get('/{submission}/file', [SubmissionController::class, 'file'])->name('file');
            Route::post('/{submission}/grade', [SubmissionController::class, 'grade'])->name('grade');
            Route::post('/return-all', [SubmissionController::class, 'returnAll'])->name('return-all');
        });
    });
});
