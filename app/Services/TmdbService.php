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
            \Log::error('TMDB Genre fetch error' . $e->getMessage());
        }
    }

    protected function fetchWatchProviders($movieId)
    {
        try {
            $response = Http::withToken($this->token)
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->get("{$this->baseUrl}/movie/{$movieId}/watch/providers");

            if ($response->successful()) {
                $data = $response->json();
                
                $countryData = $data['results']['NL'] ?? $data['results']['US'] ?? [];
                $link = $countryData['link'] ?? ''; 
                
                $allProviders = collect();
                if (!empty($countryData['flatrate'])) {
                    $allProviders = $allProviders->concat($countryData['flatrate']);
                }
                if (!empty($countryData['free'])) {
                    $allProviders = $allProviders->concat($countryData['free']);
                }
                if (!empty($countryData['rent'])) {
                    $allProviders = $allProviders->concat($countryData['rent']);
                }
                if (!empty($countryData['buy'])) {
                    $allProviders = $allProviders->concat($countryData['buy']);
                }
                
                return [
                    'providers' => $allProviders
                        ->unique('provider_id')
                        ->map(function ($provider) {
                            return [
                                'name' => $provider['provider_name'],
                                'logo' => 'https://image.tmdb.org/t/p/original' . $provider['logo_path']
                            ];
                        })
                        ->values()
                        ->toArray(),
                    'link' => $link
                ];
            }
            return [];
        } catch (\Exception $e) {
            \Log::error('TMDB Watch Providers Error: ' . $e->getMessage());
            return [];
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
                return ['error' => 'Geen films gevonden'];
            }

            $movie = $results[array_rand($results)];
            
            $genres = collect($movie['genre_ids'] ?? [])
                ->take(3)
                ->map(function ($genreId) {
                    return ['name' => $this->genres[$genreId] ?? 'Unknown'];
                })
                ->values()
                ->toArray();

            $watchProvidersData = $this->fetchWatchProviders($movie['id']);

            return [
                'title' => $movie['title'] ?? '',
                'overview' => $movie['overview'] ?? '',
                'poster_full' => !empty($movie['poster_path']) ? 'https://image.tmdb.org/t/p/w500' . $movie['poster_path'] : null,
                'release_date' => $movie['release_date'] ?? '',
                'vote_average' => $movie['vote_average'] ?? null,
                'vote_count' => $movie['vote_count'] ?? 0,
                'genres' => $genres,
                'watch_providers' => $watchProvidersData['providers'] ?? [],
                'watch_link' => $watchProvidersData['link'] ?? ''
            ];
        } catch (\Exception $e) {
            \Log::error('TMDB Service Error: ' . $e->getMessage());
            return ['error' => 'Error fetching movie: ' . $e->getMessage()];
        }
    }
}
