<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $movie['title'] ?? 'Film Details' }}</title>
<link rel="stylesheet" href="/css/moviepicker.css">
<style>
body {
    font-family: system-ui, Arial;
    margin: 24px;
    background-color: rgb(192, 192, 192);
}

h1 {
    text-align: center;
    margin-bottom: 20px;
}

.movie-card {
    max-width: 1000px;
    margin: 0 auto 40px;
    background-color: #b1b1b1;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.movie-card:hover {
    background-color: #c8c8c8;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    transform: translateY(-2px);
}

.poster img {
    width: 100%;
    max-height: 500px;
    object-fit: cover;
    border-radius: 12px;
    margin-bottom: 20px;
}

.details p {
    margin: 8px 0;
}

.details .rating {
    font-weight: bold;
}

.provider-logos {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 8px;
}

.provider-logo img {
    border: 2px solid #fff;
    border-radius: 8px;
    max-width: 40px;
    height: auto;
    transition: all 0.2s;
}

.provider-logo:hover img {
    transform: scale(1.1);
    border-color: #4caf50;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

a.primary-btn {
    background-color: #4caf50;
    color: white;
    font-weight: bold;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    text-decoration: none;
    display: inline-block;
    margin-top: 10px;
}

a.primary-btn:hover {
    background-color: #3e8f41;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}

.cast {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
}

.cast-member {
    width: 100px;
    text-align: center;
}

.cast-member img {
    width: 100px;
    border-radius: 8px;
}

.gallery {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    margin-top: 20px;
}

.gallery img {
    width: 200px;
    border-radius: 12px;
}

iframe {
    width: 100%;
    max-width: 560px;
    height: 315px;
    border-radius: 12px;
    margin-top: 10px;
}
</style>
</head>
<body>

<h1>{{ $movie['title'] ?? 'Film Details' }}</h1>

<div class="movie-card">

    {{-- Poster bovenaan --}}
    @if(!empty($movie['poster_full']))
    <div class="poster">
        <img src="{{ $movie['poster_full'] }}" alt="Poster {{ $movie['title'] ?? '' }}">
    </div>
    @endif

    {{-- Details --}}
    <div class="details">
        <p><strong>Beschrijving:</strong> {{ $movie['overview'] ?? 'Geen beschrijving beschikbaar.' }}</p>
        <p><strong>Regisseur:</strong> {{ $movie['director'] ?? 'Onbekend' }}</p>
        <p><strong>Release:</strong> {{ $movie['release_date'] ?? 'N/A' }}</p>
        <p class="rating"><strong>Rating:</strong> 
            @if(!empty($movie['vote_average']))
                {{ round($movie['vote_average']*10,1) }}%
            @else
                N/A
            @endif
        </p>

        @if(!empty($movie['genres']))
            <p><strong>Genres:</strong> {{ implode(', ', array_map(fn($g) => $g['name'] ?? 'Onbekend', $movie['genres'])) }}</p>
        @endif

        {{-- Watch providers --}}
        @if(!empty($movie['watch_providers']))
            <p><strong>Beschikbaar op:</strong></p>
            <div class="provider-logos">
                @foreach($movie['watch_providers'] as $provider)
                    <a href="{{ $movie['watch_link'] ?? '#' }}" target="_blank" class="provider-logo" title="{{ $provider['name'] }}">
                        <img src="{{ $provider['logo'] ?? '' }}" alt="{{ $provider['name'] }}">
                    </a>
                @endforeach
            </div>
            @if(!empty($movie['watch_link']))
                <a href="{{ $movie['watch_link'] }}" target="_blank" class="primary-btn">Bekijk hier de film</a>
            @endif
        @else
            <p><strong>Niet beschikbaar om te streamen</strong></p>
        @endif

        {{-- Trailer --}}
        @if(!empty($movie['trailer']))
            <h3>Trailer</h3>
            <iframe src="{{ $movie['trailer'] }}" title="Trailer {{ $movie['title'] ?? '' }}" frameborder="0" allowfullscreen></iframe>
        @endif

        {{-- Cast --}}
        @if(!empty($movie['credits']['cast']))
            <h3>Hoofdcast</h3>
            <div class="cast">
                @foreach(array_slice($movie['credits']['cast'], 0, 6) as $cast)
                    <div class="cast-member">
                        @if(!empty($cast['profile_path']))
                            <img src="https://image.tmdb.org/t/p/w200{{ $cast['profile_path'] }}" alt="{{ $cast['name'] }}">
                        @endif
                        <p>{{ $cast['name'] ?? 'Onbekend' }}</p>
                        <p style="font-size: 0.9em;">als {{ $cast['character'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Gallery onderaan --}}
    @if(!empty($movie['images']['backdrops']))
        <h3 style="text-align:center;">Foto's / Galerij</h3>
        <div class="gallery">
            @foreach(array_slice($movie['images']['backdrops'], 0, 8) as $image)
                <img src="https://image.tmdb.org/t/p/w500{{ $image['file_path'] ?? '' }}" alt="Movie image">
            @endforeach
        </div>
    @endif

</div>
</body>
</html>
