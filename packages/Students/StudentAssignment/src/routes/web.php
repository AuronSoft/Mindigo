<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentAssignment\Http\Controllers\AssignmentController;

Route::middleware(['web', 'auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/', [AssignmentController::class, 'index'])->name('index');
        Route::get('/{assignment}', [AssignmentController::class, 'show'])->name('show');
        Route::post('/{assignment}/submit', [AssignmentController::class, 'submit'])->name('submit');
        Route::get('/{assignment}/files/{fileIndex}', [AssignmentController::class, 'assignmentFile'])->whereNumber('fileIndex')->name('files.show');
        Route::get('/{assignment}/submission-file', [AssignmentController::class, 'submissionFile'])->name('submission-file.show');
    });
});
