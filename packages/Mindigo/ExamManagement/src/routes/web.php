<?php

use Illuminate\Support\Facades\Route;
use Mindigo\ExamManagement\Http\Controllers\ExamAnalyticsController;
use Mindigo\ExamManagement\Http\Controllers\ExamAttemptController;
use Mindigo\ExamManagement\Http\Controllers\ExamController;
use Mindigo\ExamManagement\Http\Controllers\ExamGradingController;
use Mindigo\ExamManagement\Http\Controllers\ExamMonitoringController;
use Mindigo\ExamManagement\Http\Controllers\ExamProctorController;
use Mindigo\ExamManagement\Http\Controllers\ExamSessionController;
use Mindigo\ExamManagement\Http\Controllers\ExamTemplateController;
use Mindigo\ExamManagement\Http\Middleware\EnsureExamBusinessRole;

Route::middleware(['web', 'auth', 'role:teacher'])
    ->prefix('teacher/exams/templates')
    ->name('teacher.exam-templates.')
    ->group(function (): void {
        Route::get('/', [ExamTemplateController::class, 'index'])->name('index');
        Route::get('/create', [ExamTemplateController::class, 'create'])->name('create');
        Route::post('/', [ExamTemplateController::class, 'store'])->name('store');
        Route::get('/{template}/edit', [ExamTemplateController::class, 'edit'])->name('edit');
        Route::put('/{template}', [ExamTemplateController::class, 'update'])->name('update');
        Route::post('/{template}/ready', [ExamTemplateController::class, 'ready'])->name('ready');
    });

Route::middleware(['web', 'auth', 'role:teacher'])
    ->prefix('teacher/exams/sessions')
    ->name('teacher.exam-sessions.')
    ->group(function (): void {
        Route::get('/', [ExamSessionController::class, 'index'])->name('index');
        Route::get('/create', [ExamSessionController::class, 'create'])->name('create');
        Route::post('/', [ExamSessionController::class, 'store'])->name('store');
        Route::get('/{session}/analytics', [ExamAnalyticsController::class, 'show'])->name('analytics.show');
        Route::get('/{session}/analytics/export/{format}', [ExamAnalyticsController::class, 'export'])->name('analytics.export')->whereIn('format', ['csv', 'pdf']);
        Route::get('/{session}/grading', [ExamGradingController::class, 'index'])->name('grading.index');
        Route::get('/{session}/grading/questions/{question}', [ExamGradingController::class, 'question'])->name('grading.question');
        Route::get('/{session}/grading/export/excel', [ExamGradingController::class, 'excel'])->name('grading.export.excel');
        Route::get('/{session}/grading/export/pdf', [ExamGradingController::class, 'pdf'])->name('grading.export.pdf');
        Route::get('/{session}/grading/{attempt}', [ExamGradingController::class, 'show'])->name('grading.show');
        Route::put('/{session}/grading/{attempt}/answers/{answer}', [ExamGradingController::class, 'grade'])->name('grading.answers.update');
        Route::put('/{session}/grading/{attempt}/answers/{answer}/autosave', [ExamGradingController::class, 'autosave'])->name('grading.answers.autosave');
        Route::post('/{session}/grading/{attempt}/release', [ExamGradingController::class, 'release'])->name('grading.release');
        Route::post('/{session}/grading/assign', [ExamGradingController::class, 'assign'])->name('grading.assign');
        Route::post('/{session}/grading/regrade', [ExamGradingController::class, 'regrade'])->name('grading.regrade');
        Route::post('/{session}/grading/appeals/{appeal}', [ExamGradingController::class, 'resolveAppeal'])->name('grading.appeals.resolve');
        Route::get('/{session}/monitoring', [ExamMonitoringController::class, 'index'])->name('monitoring.index');
        Route::get('/{session}/monitoring/data', [ExamMonitoringController::class, 'data'])->name('monitoring.data');
        Route::post('/{session}/attempts/{attempt}/add-time', [ExamMonitoringController::class, 'addTime'])->name('monitoring.add-time');
        Route::post('/{session}/attempts/{attempt}/retry', [ExamMonitoringController::class, 'retry'])->name('monitoring.retry');
        Route::post('/{session}/attempts/{attempt}/warning', [ExamMonitoringController::class, 'warning'])->name('monitoring.warning');
        Route::post('/{session}/attempts/{attempt}/pause', [ExamMonitoringController::class, 'pause'])->name('monitoring.pause');
        Route::post('/{session}/attempts/{attempt}/resume', [ExamMonitoringController::class, 'resume'])->name('monitoring.resume');
        Route::post('/{session}/attempts/{attempt}/proctor-note', [ExamProctorController::class, 'note'])->name('proctor.note');
        Route::post('/{session}/attempts/{attempt}/terminate', [ExamProctorController::class, 'terminate'])->name('proctor.terminate');
    });

Route::middleware(['web', 'auth', 'role:admin'])
    ->get('dashboard/exam-operations', [ExamAnalyticsController::class, 'operations'])
    ->name('admin.exam-operations');

Route::middleware([
    'web',
    'auth',
])
    ->prefix('dashboard/exams')
    ->name('exams.')
    ->group(function () {
        Route::get('/', [ExamController::class, 'index'])->name('index');

        Route::middleware(EnsureExamBusinessRole::class)->group(function () {
            Route::get('/create', [ExamController::class, 'create'])->name('create');
            Route::post('/', [ExamController::class, 'store'])->name('store');
            Route::get('/attempts/{attempt}', [ExamAttemptController::class, 'take'])->name('attempts.take');
            Route::post('/attempts/{attempt}/autosave', [ExamAttemptController::class, 'autosave'])->name('attempts.autosave');
            Route::post('/attempts/{attempt}/submit', [ExamAttemptController::class, 'submit'])->name('attempts.submit');
            Route::post('/attempts/{attempt}/violation', [ExamAttemptController::class, 'logViolation'])->name('attempts.violation');
            Route::get('/attempts/{attempt}/result', [ExamAttemptController::class, 'result'])->name('attempts.result');
            Route::get('/{exam}', [ExamController::class, 'show'])->name('show');
            Route::get('/{exam}/edit', [ExamController::class, 'edit'])->name('edit');
            Route::put('/{exam}', [ExamController::class, 'update'])->name('update');
            Route::post('/{exam}/publish', [ExamController::class, 'publish'])->name('publish');
            Route::post('/{exam}/close', [ExamController::class, 'close'])->name('close');
            Route::post('/{exam}/start', [ExamAttemptController::class, 'start'])->name('start');
            Route::delete('/{exam}', [ExamController::class, 'destroy'])->name('destroy');
        });
    });
