<?php

use Illuminate\Support\Facades\Route;
use Mindigo\LearningTools\Http\Controllers\LearningNoteController;
use Mindigo\LearningTools\Http\Controllers\LearningResourceController;
use Mindigo\LearningTools\Http\Controllers\LearningToolsController;
use Mindigo\LearningTools\Http\Controllers\PomodoroController;

Route::middleware(['web', 'auth', 'role:student|teacher|admin', 'permission:learning-tools.view'])
    ->prefix('learning-tools')
    ->name('learning-tools.')
    ->group(function (): void {
        Route::get('/', [LearningToolsController::class, 'index'])->name('index');

        Route::middleware('permission:learning-resources.manage')->group(function (): void {
            Route::get('/resources/create', [LearningResourceController::class, 'create'])->name('resources.create');
            Route::post('/resources', [LearningResourceController::class, 'store'])->name('resources.store');
        });

        Route::middleware('permission:learning-tools.use')->group(function (): void {
            Route::get('/pomodoro', [PomodoroController::class, 'index'])->name('pomodoro.index');
            Route::post('/pomodoro', [PomodoroController::class, 'store'])->name('pomodoro.store');
            Route::patch('/pomodoro/{session}/complete', [PomodoroController::class, 'complete'])->name('pomodoro.complete');
            Route::patch('/pomodoro/{session}/cancel', [PomodoroController::class, 'cancel'])->name('pomodoro.cancel');

            Route::resource('notes', LearningNoteController::class)->except('show');

            Route::get('/resources', [LearningResourceController::class, 'index'])->name('resources.index');
            Route::get('/resources/{resource}', [LearningResourceController::class, 'show'])->name('resources.show');
            Route::post('/resources/{resource}/favorite', [LearningResourceController::class, 'favorite'])->name('resources.favorite');
        });

        Route::middleware('permission:learning-resources.manage')->group(function (): void {
            Route::get('/resources/{resource}/edit', [LearningResourceController::class, 'edit'])->name('resources.edit');
            Route::put('/resources/{resource}', [LearningResourceController::class, 'update'])->name('resources.update');
            Route::delete('/resources/{resource}', [LearningResourceController::class, 'destroy'])->name('resources.destroy');
        });
    });
