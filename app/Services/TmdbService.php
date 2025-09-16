<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TmdbService 
{
    protected $apiToken;
    protected $baseUrl = 'https://api.themoviedb.org/3';
    protected $token;

    public function __construct()
    {
        $this->token = config('services.tmdb.token');
    }

    public function getRandomMovie()
    {
        try {
            // Get a random page of popular movies (TMDB has up to 500 pages)
            $page = rand(1, 20); // Let's start with first 20 pages to be safe
            
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
            
            return [
                'title' => $movie['title'] ?? '',
                'overview' => $movie['overview'] ?? '',
                'poster_full' => !empty($movie['poster_path']) ? 'https://image.tmdb.org/t/p/w500' . $movie['poster_path'] : null,
                'release_date' => $movie['release_date'] ?? '',
                'rating' => $movie['vote_average'] ?? '',
            ];
        } catch (\Exception $e) {
            \Log::error('TMDB Service Error: ' . $e->getMessage());
            return ['error' => 'Error fetching movie: ' . $e->getMessage()];
        }
    }
}
