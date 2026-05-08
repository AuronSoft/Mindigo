<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShowController;
use Illuminate\Support\Facades\Route;

// Public Routes

Route::get('/movies', [MovieController::class, "index"])->name("movies.index");
Route::get('/movies/{movie:slug}', [MovieController::class, "detail"])->name("movies.detail");

Route::get('/shows', [ShowController::class, "index"])->name("shows.index");

// Guests Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name("login");
    Route::post('/login', [AuthController::class, "login"]);

    Route::get('/register', [AuthController::class, "showRegister"])->name("register");
    Route::post('/register', [AuthController::class, "register"]);
});
// Authenticated User Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, "logout"])->name('logout');
    Route::post('/reviews', [MovieController::class, 'reviewstore'])->name('reviews.store');
    Route::get('/profile', [ProfileController::class, "index"])->name("profile.index");

    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
});


Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'show'])->name("dashboard");

    // Manage Movies
    Route::get('/movies', [AdminController::class, 'showMovies'])->name("movies.index");
    Route::get('/movies/create', [AdminController::class, 'createMovie'])->name("movies.create");
    Route::post('/movies', [AdminController::class, 'storeMovie'])->name("movies.store");
    Route::get('/movies/{movie:slug}/edit', [AdminController::class, 'editMovie'])->name("movies.edit");
    Route::put('/movies/{movie:slug}', [AdminController::class, 'updateMovie'])->name("movies.update");
    Route::delete('/movies/{movie:slug}', [AdminController::class, 'destroyMovie'])->name("movies.destroy");

    // Manage Shows
    Route::get('/shows', [AdminController::class, 'showShows'])->name("shows.index");
    Route::get('/shows/create', [AdminController::class, 'createShow'])->name("shows.create");
    Route::post('/shows', [AdminController::class, 'storeShow'])->name("shows.store");
    Route::get('/shows/{show}/edit', [AdminController::class, 'editShow'])->name("shows.edit");
    Route::put('/shows/{show}', [AdminController::class, 'updateShow'])->name("shows.update");
    Route::delete('/shows/{show}', [AdminController::class, 'destroyShow'])->name("shows.destroy");

    // Manage Users
    Route::get('/reviews', [AdminController::class, 'showReviews'])->name("reviews.index");

    // Manage Users
    Route::get('/customers', [AdminController::class, 'listUsers'])->name("users.index");
});
