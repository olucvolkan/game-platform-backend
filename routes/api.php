<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AutocompleteController;
use App\Http\Controllers\Api\FavoritesController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\GameListController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
|
*/

// Game list with pagination, filtering, and search
Route::get('/list', GameListController::class)->name('games.list');

// Search autocomplete
Route::get('/autocomplete', AutocompleteController::class)->name('games.autocomplete');

// Single game details
Route::get('/games/{slug}', [GameController::class, 'show'])->name('games.show');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

/*
|--------------------------------------------------------------------------
| Protected Routes (require authentication)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/user', [AuthController::class, 'user'])->name('auth.user');

    // Favorites
    Route::get('/favorites', [FavoritesController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{gameId}', [FavoritesController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites/{gameId}', [FavoritesController::class, 'destroy'])->name('favorites.destroy');
});
