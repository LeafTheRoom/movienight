<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TmdbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MoviePickerController extends Controller
{
    public function __construct(protected TmdbService $tmdb) {}

    public function random(): JsonResponse
    {
        try {
            Log::info('Fetching random movie from TMDB...');
            $movie = $this->tmdb->getRandomMovie();
            
            if (isset($movie['error'])) {
                Log::error('TMDB error: ' . $movie['error']);
                return response()->json($movie, 500);
            }
            
            return response()->json($movie);
        } catch (\Exception $e) {
            Log::error('MoviePicker error: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching movie: ' . $e->getMessage()], 500);
        }
    }
}
