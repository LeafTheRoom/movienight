<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Random Movie Picker</title>
  <link rel="stylesheet" href="/css/moviepicker.css">
</head>
<body>
  <h1>Random Movie Picker &#127916;</h1>
  <div id="result"></div>
  <div class="justify-center">
    <div class="gap">
  <button class="primary-btn" id="pick">Kies</button>
  </div>
</div>
  <script>
    const btn = document.getElementById('pick');
    const result = document.getElementById('result');

    btn.addEventListener('click', async () => {
      try {
        const res = await fetch('/movies/random');
        const data = await res.json();

        if (data.error) {
          result.innerHTML = `<p>Fout: ${data.error}</p>`;
        } else {
          const poster = data.poster_full ?? '';
          result.innerHTML = `
            <div class="card">
              <div>
                ${poster ? `<img src="${poster}" alt="poster">` : ''}
              </div>
              <div>
                <h2>${data.title ?? 'Geen titel'}</h2>
                <p><strong>Genre:</strong> ${data.genres ? data.genres.map(g => g.name).join(', ') : 'N/A'}</p>
                <p><strong>Release:</strong> ${data.release_date ?? 'N/A'}</p>
                <p>${data.overview ?? ''}</p>
                <p><strong>Rating:</strong> ${data.vote_average ? `${(data.vote_average * 10).toFixed(1)}%` : 'N/A'}</p>
              </div>
            </div>
          `;
        }
      } catch (e) {
        result.innerHTML = `<p>Network error: ${e.message}</p>`;
      } finally {
        btn.disabled = false;
        btn.textContent = 'Kies';
      }
    });
  </script>
</body>
</html>
