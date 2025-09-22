<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TmdbService 
{
    protected $apiToken;
    protected $baseUrl = 'https://api.themoviedb.org/3';
    protected $token;

    protected $genres = [];
    protected $watchProviders = [];

    public function __construct() // De service constructeert de token en haalt genres en providers op
    {
        $this->token = config('services.tmdb.token');
        $this->fetchGenres();
        $this->fetchAvailableWatchProviders();
    }

    public function getGenres()  
    {
        return $this->genres; // Retourneer de genres als een array van id => naam
    }

    public function getWatchProviders() // Retourneer de watch providers
    {
        return $this->watchProviders;
    }

    protected function fetchAvailableWatchProviders() 
    {
        try { // Probeer om de beschikbare streamingsdiensten op te halen
            $response = Http::withToken($this->token)
                ->withHeaders(['Accept' => 'application/json'])
                ->get("{$this->baseUrl}/watch/providers/movie", [
                    'watch_region' => 'NL'
                ]);

            if ($response->successful()) { // Als de response succesvol is, verwerk de data
                $data = $response->json();
                $this->watchProviders = collect($data['results'] ?? [])
                    ->map(function ($provider) {
                        return [
                            'id' => $provider['provider_id'],
                            'name' => $provider['provider_name'], 
                            'logo' => "https://image.tmdb.org/t/p/original{$provider['logo_path']}"
                        ];
                    })
                    ->toArray();
            }
        } catch (\Exception $e) {
            \Log::error('TMDB Watch Providers fetch error: ' . $e->getMessage());
        }
    }

    protected function fetchGenres()
    {
        try { // Probeer om de genres op te halen
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
        try { // Probeer om de watch providers voor een specifieke film op te halen
            $response = Http::withToken($this->token)
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->get("{$this->baseUrl}/movie/{$movieId}/watch/providers");

            if ($response->successful()) {
                $data = $response->json();
                
                $countryData = $data['results']['NL'] ?? $data['results']['US'] ?? [];
                $link = $countryData['link'] ?? ''; 
                
                $allProviders = collect(); // Verzamel alle soorten providers
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
                
                return [ // Retourneer de unieke providers met hun naam en logo
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

    public function getRandomMovie($filters = [])
    {
        try { // Probeer om een willekeurige film op te halen op met de filters
            $fetchMovies = function($params) {
                $response = Http::withToken($this->token)
                    ->withHeaders(['Accept' => 'application/json'])
                    ->get("{$this->baseUrl}/discover/movie", $params);

                if (!$response->successful()) {
                    \Log::error('TMDB Error: ' . $response->body());
                    return null;
                }

                $data = $response->json();
                return $data['results'] ?? [];
            };

            $baseParams = [
                'language' => 'en-US',
                'watch_region' => 'NL',
            ];

            $params = $baseParams; 
            
            $initialResponse = Http::withToken($this->token)  
                ->withHeaders(['Accept' => 'application/json'])
                ->get("{$this->baseUrl}/discover/movie", array_merge($params, ['page' => 1]));
            
            $totalPages = $initialResponse->json()['total_pages'] ?? 1;
            $page = $totalPages > 1 ? rand(1, min($totalPages, 20)) : 1;
            $params['page'] = $page;

            // Filters toepassen

            if (!empty($filters['genres'])) {
                $params['with_genres'] = implode(',', $filters['genres']);
            }

            $results = $fetchMovies($params); 
            if (empty($results)) {
                unset($params['with_genres']);
            }

            if (!empty($filters['providers']) && !empty($results)) { 
                $params['with_watch_providers'] = implode('|', $filters['providers']);
                $tempResults = $fetchMovies($params);
                if (!empty($tempResults)) $results = $tempResults;
            }

            if (!empty($filters['minRating']) && !empty($results)) {
                $params['vote_average.gte'] = $filters['minRating'];
                $tempResults = $fetchMovies($params);
                if (!empty($tempResults)) $results = $tempResults;
            }

            if (!empty($filters['maxRating']) && !empty($results)) {
                $params['vote_average.lte'] = $filters['maxRating'];
                $tempResults = $fetchMovies($params);
                if (!empty($tempResults)) $results = $tempResults;
            }

            if (!empty($filters['fromYear']) && !empty($results)) {
                $params['primary_release_date.gte'] = $filters['fromYear'] . '-01-01';
                $tempResults = $fetchMovies($params);
                if (!empty($tempResults)) $results = $tempResults;
            }

            if (!empty($filters['toYear']) && !empty($results)) {
                $params['primary_release_date.lte'] = $filters['toYear'] . '-12-31';
                $tempResults = $fetchMovies($params);
                if (!empty($tempResults)) $results = $tempResults;
            }

            if (empty($results)) {
                $results = $fetchMovies($baseParams);
            }

            $movie = $results[array_rand($results)]; // Kies een willekeurige film uit de resultaten
            
            $genres = collect($movie['genre_ids'] ?? [])
                ->take(3)
                ->map(function ($genreId) {
                    return ['name' => $this->genres[$genreId] ?? 'Unknown'];
                })
                ->values()
                ->toArray();

            $watchProvidersData = $this->fetchWatchProviders($movie['id']);



            return [ // Retourneer de filmgegevens in een een goede structuur
                'id' => $movie['id'],  
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

    public function getWatchProvidersForMovie($movieId)
{
    return $this->fetchWatchProviders($movieId);
}
}
