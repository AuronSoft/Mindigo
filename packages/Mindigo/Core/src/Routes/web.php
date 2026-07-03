<?php

use Mindigo\Core\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/technical-support-policy', [HomeController::class, 'technicalSupportPolicy'])->name('technical-support-policy');
Route::get('/ai-assistant-policy', [HomeController::class, 'aiAssistantPolicy'])->name('ai-assistant-policy');
Route::get('/refund-policy', [HomeController::class, 'refundPolicy'])->name('refund-policy');
Route::get('/exam-tips', [HomeController::class, 'examTips'])->name('exam-tips');
