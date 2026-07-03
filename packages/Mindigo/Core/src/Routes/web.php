<?php

use Mindigo\Core\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
