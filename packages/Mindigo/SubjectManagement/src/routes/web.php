<?php

use Illuminate\Support\Facades\Route;
use Mindigo\SubjectManagement\Http\Controllers\SubjectController;

Route::middleware(['web', 'auth'])
    ->prefix('dashboard/subjects')
    ->name('subjects.')
    ->group(function () {
        Route::get('/', [SubjectController::class, 'index'])->name('index');
        Route::get('/create', [SubjectController::class, 'create'])->name('create');
        Route::post('/', [SubjectController::class, 'store'])->name('store');
        Route::get('/{subject}', [SubjectController::class, 'show'])->name('show');
        Route::get('/{subject}/edit', [SubjectController::class, 'edit'])->name('edit');
        Route::put('/{subject}', [SubjectController::class, 'update'])->name('update');
        Route::delete('/{subject}', [SubjectController::class, 'destroy'])->name('destroy');
        Route::post('/{subject}/topics', [SubjectController::class, 'storeTopic'])->name('topics.store');
        Route::put('/topics/{topic}', [SubjectController::class, 'updateTopic'])->name('topics.update');
        Route::delete('/topics/{topic}', [SubjectController::class, 'destroyTopic'])->name('topics.destroy');
    });
