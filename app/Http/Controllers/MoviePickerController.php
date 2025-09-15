<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Services\TmdbService;
use Illuminate\Http\JsonResponse;


class MoviePickerController extends Controller
{
public function __construct(protected TmdbService $tmdb) {}


public function random(): JsonResponse
{
$movie = $this->tmdb->getRandomMovie();
return response()->json($movie);
}
}