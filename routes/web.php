<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MoviePickerController;

Route::get('/', function () {
    return view('moviepicker');
});

Route::get('/movies/random', [MoviePickerController::class, 'random']);
Route::view('/moviepicker', 'moviepicker');

