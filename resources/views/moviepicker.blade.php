<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Random Movie Picker</title>
  <style>
    body { font-family: system-ui, Arial; margin: 24px; }
    img { max-width: 300px; border-radius: 8px; }
    .card { display: flex; gap: 16px; align-items: flex-start; margin-top: 16px; }
    .card div { max-width: 600px; }
  </style>
</head>
<body>
  <h1>Random Movie Picker</h1>
  <button id="pick">Kies</button>
  <div id="result"></div>

  <script>
    const btn = document.getElementById('pick');
    const result = document.getElementById('result');

    btn.addEventListener('click', async () => {
      btn.disabled = true;
      btn.textContent = 'Even kiezen...';
      result.innerHTML = '';

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
                <p><strong>Release:</strong> ${data.release_date ?? 'N/A'}</p>
                <p>${data.overview ?? ''}</p>
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
