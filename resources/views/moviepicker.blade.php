<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Random Movie Picker</title>
    <link rel="stylesheet" href="/css/moviepicker.css">
</head>
<body>
    <h1>Random Movie Picker &#127916;</h1>
    
    <div class="filters">
        <div class="filter-group">
            <label for="genre">Genre:</label>
            <select id="genre" multiple>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="provider">Streamingdiensten:</label>
            <select id="provider" multiple>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Rating:</label>
            <div class="rating-range">
                <input type="number" id="minRating" min="0" max="10" step="0.5" value="0">
                <span>to</span>
                <input type="number" id="maxRating" min="0" max="10" step="0.5" value="10">
            </div>
        </div>
        
        <div class="filter-group">
            <label>Releasejaar:</label>
            <div class="year-range">
                <input type="number" id="fromYear" min="1900" max="2025" value="1900">
                <span>to</span>
                <input type="number" id="toYear" min="1900" max="2025" value="2025">
            </div>
        </div>
    </div>

    <div id="result"></div>

    <div class="justify-center">
        <div class="gap">
            <button class="primary-btn" id="pick">Kies</button>
        </div>
    </div>

    <script> // Kies film op basis van filters
        const btn = document.getElementById('pick');
        const result = document.getElementById('result');
        const genreSelect = document.getElementById('genre');
        const providerSelect = document.getElementById('provider');
        const minRating = document.getElementById('minRating');
        const maxRating = document.getElementById('maxRating');
        const fromYear = document.getElementById('fromYear');
        const toYear = document.getElementById('toYear');

        async function initializeFilters() {
            try {
                const [genresRes, providersRes] = await Promise.all([
                    fetch('/movies/genres'),
                    fetch('/movies/providers')
                ]);
                
                const genres = await genresRes.json();
                const providers = await providersRes.json();

                genreSelect.innerHTML = Object.entries(genres)
                    .map(([id, name]) => `<option value="${id}">${name}</option>`)
                    .join('');

                providerSelect.innerHTML = providers
                    .map(provider => `<option value="${provider.id}">${provider.name}</option>`)
                    .join('');
            } catch (error) {
                console.error('Error loading filters:', error);
            }
        }

        initializeFilters();

        btn.addEventListener('click', async () => { 
            try {
                const filters = { // Verzamel de geselecteerde filters
                    genres: Array.from(genreSelect.selectedOptions).map(opt => opt.value),
                    providers: Array.from(providerSelect.selectedOptions).map(opt => opt.value),
                    minRating: minRating.value,
                    maxRating: maxRating.value,
                    fromYear: fromYear.value,
                    toYear: toYear.value
                };

                const res = await fetch('/movies/random?' + new URLSearchParams({
                    filters: JSON.stringify(filters)
                }));
                const data = await res.json();

                if (data.error) {
                    result.innerHTML = `<p>Fout: ${data.error}</p>`;
                    return;
                }

                if (!data.id) {
                    result.innerHTML = `<p>Fout: Geen film-id beschikbaar</p>`;
                    return;
                }

                result.innerHTML = `
                    <div class="card">
                        <div>${data.poster_full ? `<img src="${data.poster_full}" alt="poster">` : ''}</div>
                        <div>
                            <h2>
                                <a href="/movies/${data.id}" target="_blank">${data.title ?? 'Geen titel'}</a>
                            </h2>
                            <p><strong>Genre:</strong> ${data.genres ? data.genres.map(g => g.name).join(', ') : 'N/A'}</p>
                            <p><strong>Release:</strong> ${data.release_date ?? 'N/A'}</p>
                            <p>${data.overview ?? ''}</p>
                            <p><strong>Rating:</strong> ${data.vote_average ? `${(data.vote_average * 10).toFixed(1)}%` : 'N/A'}</p>
                            ${data.watch_providers?.length ? `
                                <div class="watch-providers">
                                    <p><strong>Beschikbaar op:</strong></p>
                                    <div class="provider-logos">
                                        ${data.watch_providers.map(provider => `
                                            <a href="${data.watch_link}" target="_blank" class="provider-logo" title="Kijk op ${provider.name}">
                                                <img src="${provider.logo}" alt="${provider.name}" width="40" height="40">
                                            </a>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : '<p><strong>Niet beschikbaar om te streamen</strong></p>'}
                        </div>
                    </div>
                `;
            } catch (e) {
                result.innerHTML = `<p>Er is iets misgegaan: ${e.message}</p>`;
            }
        });
    </script>
</body>
</html>
