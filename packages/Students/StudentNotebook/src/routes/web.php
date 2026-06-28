<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentNotebook\Http\Controllers\NotebookController;

Route::middleware(['web', 'auth', 'role:student|admin'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('notebook')->name('notebook.')->group(function () {
        Route::get('/', [NotebookController::class, 'index'])->name('index');
        Route::post('/', [NotebookController::class, 'store'])->name('store');
        Route::put('/{note}', [NotebookController::class, 'update'])->name('update');
        Route::delete('/{note}', [NotebookController::class, 'destroy'])->name('destroy');
    });
});
