<?php

namespace App\Http\Controllers;

use App\Services\TmdbService;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    protected $tmdbService;

    public function __construct(TmdbService $tmdbService)
    {
        $this->tmdbService = $tmdbService;
    }

    public function random(Request $request)
    {
        $filters = json_decode($request->get('filters', '{}'), true);
        return $this->tmdbService->getRandomMovie($filters);
    }

    public function genres()
    {
        return response()->json($this->tmdbService->getGenres());
    }

    public function providers()
    {
        return response()->json($this->tmdbService->getWatchProviders());
    }
}