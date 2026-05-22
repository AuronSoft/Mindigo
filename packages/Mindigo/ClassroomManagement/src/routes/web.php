<?php

use Illuminate\Support\Facades\Route;
use Mindigo\ClassroomManagement\Http\Controllers\ClassroomController;

Route::middleware(['web', 'auth'])
    ->prefix('dashboard/classrooms')
    ->name('classrooms.')
    ->group(function () {
        Route::get('/', [ClassroomController::class, 'index'])->name('index');
        Route::get('/create', [ClassroomController::class, 'create'])->name('create');
        Route::post('/', [ClassroomController::class, 'store'])->name('store');
        Route::get('/{classroom}', [ClassroomController::class, 'show'])->name('show');
        Route::get('/{classroom}/edit', [ClassroomController::class, 'edit'])->name('edit');
        Route::put('/{classroom}', [ClassroomController::class, 'update'])->name('update');
        Route::delete('/{classroom}', [ClassroomController::class, 'destroy'])->name('destroy');
        Route::post('/{classroom}/students', [ClassroomController::class, 'syncStudents'])->name('students.sync');
        Route::post('/{classroom}/subjects', [ClassroomController::class, 'syncSubjects'])->name('subjects.sync');
    });
