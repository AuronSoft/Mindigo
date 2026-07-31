<?php

use Illuminate\Support\Facades\Route;
use Mindigo\LearningTools\Http\Controllers\AcademicScoreController;
use Mindigo\LearningTools\Http\Controllers\AdmissionLookupController;
use Mindigo\LearningTools\Http\Controllers\AiTutorController;
use Mindigo\LearningTools\Http\Controllers\FlashcardController;
use Mindigo\LearningTools\Http\Controllers\GpaCalculatorController;
use Mindigo\LearningTools\Http\Controllers\KnowledgeGapController;
use Mindigo\LearningTools\Http\Controllers\LearningNoteController;
use Mindigo\LearningTools\Http\Controllers\LearningResourceController;
use Mindigo\LearningTools\Http\Controllers\LearningToolsController;
use Mindigo\LearningTools\Http\Controllers\MistakeNotebookController;
use Mindigo\LearningTools\Http\Controllers\PersonalizedPracticeController;
use Mindigo\LearningTools\Http\Controllers\PomodoroController;
use Mindigo\LearningTools\Http\Controllers\ScoreCalculatorController;
use Mindigo\LearningTools\Http\Controllers\StudyPlanController;

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

            Route::get('/flashcards', [FlashcardController::class, 'index'])->name('flashcards.index');
            Route::get('/flashcards/create', [FlashcardController::class, 'create'])->name('flashcards.create');
            Route::post('/flashcards', [FlashcardController::class, 'store'])->name('flashcards.store');
            Route::get('/flashcards/{deck}', [FlashcardController::class, 'show'])->name('flashcards.show');
            Route::get('/flashcards/{deck}/edit', [FlashcardController::class, 'edit'])->name('flashcards.edit');
            Route::put('/flashcards/{deck}', [FlashcardController::class, 'update'])->name('flashcards.update');
            Route::delete('/flashcards/{deck}', [FlashcardController::class, 'destroy'])->name('flashcards.destroy');
            Route::post('/flashcards/{deck}/cards', [FlashcardController::class, 'storeCard'])->name('flashcards.cards.store');
            Route::delete('/flashcards/{deck}/cards/{card}', [FlashcardController::class, 'destroyCard'])->name('flashcards.cards.destroy');
            Route::get('/flashcards/{deck}/study', [FlashcardController::class, 'study'])->name('flashcards.study');
            Route::post('/flashcards/{deck}/study/{card}', [FlashcardController::class, 'review'])->name('flashcards.review');

            Route::get('/plans', [StudyPlanController::class, 'index'])->name('plans.index');
            Route::get('/plans/create', [StudyPlanController::class, 'create'])->name('plans.create');
            Route::post('/plans', [StudyPlanController::class, 'store'])->name('plans.store');
            Route::get('/plans/{plan}', [StudyPlanController::class, 'show'])->name('plans.show');
            Route::get('/plans/{plan}/edit', [StudyPlanController::class, 'edit'])->name('plans.edit');
            Route::put('/plans/{plan}', [StudyPlanController::class, 'update'])->name('plans.update');
            Route::delete('/plans/{plan}', [StudyPlanController::class, 'destroy'])->name('plans.destroy');
            Route::post('/plans/{plan}/tasks', [StudyPlanController::class, 'storeTask'])->name('plans.tasks.store');
            Route::delete('/plans/{plan}/tasks/{task}', [StudyPlanController::class, 'destroyTask'])->name('plans.tasks.destroy');
            Route::post('/plans/{plan}/tasks/{task}/toggle', [StudyPlanController::class, 'toggleTask'])->name('plans.tasks.toggle');

            Route::get('/resources', [LearningResourceController::class, 'index'])->name('resources.index');
            Route::get('/resources/{resource}', [LearningResourceController::class, 'show'])->name('resources.show');
            Route::post('/resources/{resource}/favorite', [LearningResourceController::class, 'favorite'])->name('resources.favorite');

            Route::get('/mistakes', [MistakeNotebookController::class, 'index'])->name('mistakes.index');
            Route::patch('/mistakes', [MistakeNotebookController::class, 'update'])->name('mistakes.update');
            Route::get('/knowledge-gaps', [KnowledgeGapController::class, 'index'])->name('gaps.index');
            Route::get('/personalized-practice', [PersonalizedPracticeController::class, 'index'])->name('personalized.index');
            Route::get('/personalized-practice/create', [PersonalizedPracticeController::class, 'create'])->name('personalized.create');
            Route::post('/personalized-practice', [PersonalizedPracticeController::class, 'store'])->name('personalized.store');
            Route::get('/personalized-practice/{set}', [PersonalizedPracticeController::class, 'show'])->name('personalized.show');
            Route::post('/personalized-practice/{set}/start', [PersonalizedPracticeController::class, 'start'])->name('personalized.start');
            Route::delete('/personalized-practice/{set}', [PersonalizedPracticeController::class, 'destroy'])->name('personalized.destroy');

            Route::get('/score-calculator', [ScoreCalculatorController::class, 'index'])->name('scores.index');
            Route::post('/score-calculator', [ScoreCalculatorController::class, 'store'])->name('scores.store');
            Route::delete('/score-calculator/{scenario}', [ScoreCalculatorController::class, 'destroy'])->name('scores.destroy');
            Route::get('/academic-score-calculator', [AcademicScoreController::class, 'index'])->name('academic.index');
            Route::post('/academic-score-calculator', [AcademicScoreController::class, 'store'])->name('academic.store');
            Route::delete('/academic-score-calculator/{scenario}', [AcademicScoreController::class, 'destroy'])->name('academic.destroy');
            Route::get('/gpa-calculator', [GpaCalculatorController::class, 'index'])->name('gpa.index');
            Route::post('/gpa-calculator', [GpaCalculatorController::class, 'store'])->name('gpa.store');
            Route::delete('/gpa-calculator/{scenario}', [GpaCalculatorController::class, 'destroy'])->name('gpa.destroy');
            Route::get('/admissions', [AdmissionLookupController::class, 'index'])->name('admissions.index');
            Route::post('/admissions/{program}/favorite', [AdmissionLookupController::class, 'favorite'])->name('admissions.favorite');

            Route::get('/ai-tutor', [AiTutorController::class, 'index'])->name('ai.index');
            Route::post('/ai-tutor', [AiTutorController::class, 'store'])->name('ai.store');
            Route::get('/ai-tutor/{conversation}', [AiTutorController::class, 'show'])->name('ai.show');
            Route::post('/ai-tutor/{conversation}/messages', [AiTutorController::class, 'send'])->middleware('throttle:20,1')->name('ai.send');
            Route::delete('/ai-tutor/{conversation}', [AiTutorController::class, 'destroy'])->name('ai.destroy');
        });

        Route::middleware('permission:learning-resources.manage')->group(function (): void {
            Route::get('/resources/{resource}/edit', [LearningResourceController::class, 'edit'])->name('resources.edit');
            Route::put('/resources/{resource}', [LearningResourceController::class, 'update'])->name('resources.update');
            Route::delete('/resources/{resource}', [LearningResourceController::class, 'destroy'])->name('resources.destroy');
        });
    });
