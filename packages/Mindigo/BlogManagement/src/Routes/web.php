<?php

use Illuminate\Support\Facades\Route;
use Mindigo\BlogManagement\Http\Controllers\NewsController;

Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/partial', [NewsController::class, 'partial'])->name('news.partial');
