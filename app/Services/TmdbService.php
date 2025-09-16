<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TmdbService 
{
    protected $apiToken;
    protected $baseUrl = 'https://api.themoviedb.org/3';
    protected $token;

    protected $genres = [];

    public function __construct()
    {
        $this->token = config('services.tmdb.token');
        $this->fetchGenres();
    }

    protected function fetchGenres()
    {
        try {
            $response = Http::withToken($this->token)
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->get("{$this->baseUrl}/genre/movie/list", [
                    'language' => 'en-US',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->genres = collect($data['genres'] ?? [])->pluck('name', 'id')->toArray();
            }
        } catch (\Exception $e) {
            \Log::error('TMDB Genre Fetch Error: ' . $e->getMessage());
        }
    }

    public function getRandomMovie()
    {
        try {
            
            $page = rand(1, 20); 
            
            $response = Http::withToken($this->token)
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->get("{$this->baseUrl}/movie/popular", [
                    'language' => 'en-US',
                    'page' => $page,
                ]);

            if (!$response->successful()) {
                \Log::error('TMDB Error: ' . $response->body());
                return ['error' => 'Error ' . $response->status() . ': ' . $response->body()];
            }

            $data = $response->json();
            $results = $data['results'] ?? [];
            
            if (empty($results)) {
                return ['error' => 'No movies found'];
            }

            $movie = $results[array_rand($results)];
            
            $genres = collect($movie['genre_ids'] ?? [])
                ->take(3)
                ->map(function ($genreId) {
                    return ['name' => $this->genres[$genreId] ?? 'Unknown'];
                })
                ->values()
                ->toArray();

            return [
                'title' => $movie['title'] ?? '',
                'overview' => $movie['overview'] ?? '',
                'poster_full' => !empty($movie['poster_path']) ? 'https://image.tmdb.org/t/p/w500' . $movie['poster_path'] : null,
                'release_date' => $movie['release_date'] ?? '',
                'vote_average' => $movie['vote_average'] ?? null,
                'vote_count' => $movie['vote_count'] ?? 0,
                'genres' => $genres
            ];
        } catch (\Exception $e) {
            \Log::error('TMDB Service Error: ' . $e->getMessage());
            return ['error' => 'Error fetching movie: ' . $e->getMessage()];
        }
    }
}
