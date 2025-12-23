<?php

use App\Http\Controllers\Api\AutocompleteController;
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
