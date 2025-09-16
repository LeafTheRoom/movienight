<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;

Route::get('/', function () {
    return view('moviepicker');
});

Route::controller(MovieController::class)->group(function () {
    Route::get('/movies/random', 'random');
    Route::get('/movies/genres', 'genres');
    Route::get('/movies/providers', 'providers');
});

Route::view('/moviepicker', 'moviepicker');

