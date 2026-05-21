<?php

use Illuminate\Support\Facades\Route;
use Mindigo\QuestionBank\Http\Controllers\QuestionBankController;

Route::middleware(['web', 'auth'])
    ->prefix('dashboard/question-bank')
    ->name('question-bank.')
    ->group(function () {
        Route::get('/', [QuestionBankController::class, 'index'])->name('index');
        Route::post('/folders', [QuestionBankController::class, 'storeFolder'])->name('folders.store');
        Route::post('/import', [QuestionBankController::class, 'import'])->name('import');
        Route::get('/create', [QuestionBankController::class, 'create'])->name('create');
        Route::post('/', [QuestionBankController::class, 'store'])->name('store');
        Route::get('/{question}', [QuestionBankController::class, 'show'])->name('show');
        Route::get('/{question}/edit', [QuestionBankController::class, 'edit'])->name('edit');
        Route::put('/{question}', [QuestionBankController::class, 'update'])->name('update');
        Route::post('/{question}/review', [QuestionBankController::class, 'review'])->name('review');
        Route::delete('/{question}', [QuestionBankController::class, 'destroy'])->name('destroy');
    });
