<?php

use Illuminate\Support\Facades\Route;
use Mindigo\StudentPractice\Http\Controllers\AdaptivePracticeController;
use Mindigo\StudentPractice\Http\Controllers\PracticeAnalyticsController;
use Mindigo\StudentPractice\Http\Controllers\PracticeController;
use Mindigo\StudentPractice\Http\Controllers\PracticeSetController;
use Mindigo\StudentPractice\Http\Controllers\PracticeSkillController;
use Mindigo\StudentPractice\Http\Controllers\SkillPracticeController;

Route::middleware(['web', 'auth', 'role:teacher'])
    ->prefix('practice/skills')
    ->name('practice.skills.')
    ->group(function (): void {
        Route::get('/', [PracticeSkillController::class, 'index'])->name('index');
        Route::get('/create', [PracticeSkillController::class, 'create'])->name('create');
        Route::post('/', [PracticeSkillController::class, 'store'])->name('store');
        Route::get('/{skill}/edit', [PracticeSkillController::class, 'edit'])->name('edit');
        Route::put('/{skill}', [PracticeSkillController::class, 'update'])->name('update');
        Route::delete('/{skill}', [PracticeSkillController::class, 'destroy'])->name('destroy');
    });

Route::middleware('web')->get('/practice/shared/{token}', [PracticeSetController::class, 'shared'])->name('practice.shared');

Route::middleware(['web', 'auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('practice')->name('practice.')->group(function () {
        Route::get('/', [PracticeController::class, 'index'])->name('index');
        Route::get('/history', [PracticeController::class, 'history'])->name('history');
        Route::get('/sets', [PracticeSetController::class, 'index'])->name('sets.index');
        Route::post('/sets', [PracticeSetController::class, 'store'])->name('sets.store');
        Route::get('/sets/{set}', [PracticeSetController::class, 'show'])->name('sets.show')->whereNumber('set');
        Route::post('/sets/{set}/start', [PracticeSetController::class, 'start'])->name('sets.start')->whereNumber('set');
        Route::post('/sets/{set}/repeat', [PracticeSetController::class, 'repeat'])->name('sets.repeat')->whereNumber('set');
        Route::patch('/sets/{set}/share', [PracticeSetController::class, 'share'])->name('sets.share')->whereNumber('set');
        Route::delete('/sets/{set}', [PracticeSetController::class, 'destroy'])->name('sets.destroy')->whereNumber('set');
        Route::get('/analytics', [PracticeAnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/adaptive', [AdaptivePracticeController::class, 'index'])->name('adaptive.index');
        Route::post('/adaptive/{skill}/start', [AdaptivePracticeController::class, 'start'])->name('adaptive.start')->whereNumber('skill');
        Route::get('/skills', [SkillPracticeController::class, 'index'])->name('skills.index');
        Route::get('/skills/{skill}', [SkillPracticeController::class, 'show'])->name('skills.show')->whereNumber('skill');
        Route::post('/skills/{skill}/start', [SkillPracticeController::class, 'start'])->name('skills.start')->whereNumber('skill');
        Route::post('/start', [PracticeController::class, 'start'])->name('start');
        Route::get('/questions/{question}', [PracticeController::class, 'show'])->name('show')->whereNumber('question');
        Route::get('/{attempt}/attempt', [PracticeController::class, 'attempt'])->name('attempt')->whereNumber('attempt');
        Route::post('/{attempt}/submit-answer', [PracticeController::class, 'submitAnswer'])->name('submit-answer')->whereNumber('attempt');
        Route::post('/{attempt}/complete', [PracticeController::class, 'complete'])->name('complete')->whereNumber('attempt');
        Route::get('/{attempt}/result', [PracticeController::class, 'result'])->name('result')->whereNumber('attempt');
    });
});
