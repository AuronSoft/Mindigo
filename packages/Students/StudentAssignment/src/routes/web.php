<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentAssignment\Http\Controllers\AssignmentController;

Route::middleware(['web', 'auth', 'role:student|admin'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/', [AssignmentController::class, 'index'])->name('index');
    });
});
