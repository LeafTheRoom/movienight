<?php

namespace App\Http\Controllers;

use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

    $movie = $this->tmdbService->getRandomMovie($filters);

    if (!isset($movie['id'])) {
        return response()->json(['error' => 'Geen film gevonden met deze filters'], 404);
    }

    return response()->json($movie);
}

    public function genres()
    {
        return response()->json($this->tmdbService->getGenres());
    }

    public function providers()
    {
        return response()->json($this->tmdbService->getWatchProviders());
    }

public function show($id)
{
    $apiKey = config('services.tmdb.key');

    $url = "https://api.themoviedb.org/3/movie/{$id}?api_key={$apiKey}&language=nl-NL&append_to_response=videos,credits,images";

    $response = Http::get($url);

    if (!$response->successful()) {
        return response()->json(['error' => 'Kon film niet laden'], 500);
    }

    $movie = $response->json();

    $director = collect($movie['credits']['crew'] ?? [])->firstWhere('job', 'Director');
    $movie['director'] = $director['name'] ?? 'Onbekend';

    $trailer = collect($movie['videos']['results'] ?? [])->firstWhere('type', 'Trailer');
    $movie['trailer'] = $trailer
        ? "https://www.youtube.com/embed/" . $trailer['key']
        : null;

   $watchProvidersData = $this->tmdbService->getWatchProvidersForMovie($id);
    $movie['watch_providers'] = $watchProvidersData['providers'] ?? [];
    $movie['watch_link'] = $watchProvidersData['link'] ?? '';

    return view('movies.show', compact('movie'));
}
}
