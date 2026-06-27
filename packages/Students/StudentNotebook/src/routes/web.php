<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentNotebook\Http\Controllers\NotebookController;

Route::middleware(['web', 'auth'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('notebook')->name('notebook.')->group(function () {
        Route::get('/', [NotebookController::class, 'index'])->name('index');
    });
});
