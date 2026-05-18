<?php

use Illuminate\Support\Facades\Route;
use Mindigo\BlogManagement\Http\Controllers\NewsController;

Route::get('/news', [NewsController::class, 'index'])->name('news.index');