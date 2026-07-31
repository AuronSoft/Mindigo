<?php

use Illuminate\Support\Facades\Route;
use Mindigo\LearningTools\Http\Controllers\LearningToolsController;

Route::middleware(['web', 'auth', 'role:student|teacher|admin', 'permission:learning-tools.view'])
    ->get('/learning-tools', [LearningToolsController::class, 'index'])
    ->name('learning-tools.index');
