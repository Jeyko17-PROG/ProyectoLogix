<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unirse - SPIKIA</title>
    <style>
        body { background: #0b0b0b; color: white; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .join-card { background: #121212; padding: 40px; border-radius: 20px; border: 1px solid #333; text-align: center; width: 90%; max-width: 400px; }
        .logo { width: 100px; margin-bottom: 20px; }
        .btn-join { background: #2563eb; color: white; border: none; padding: 15px; border-radius: 10px; width: 100%; font-weight: bold; cursor: pointer; text-transform: uppercase; }
        select { width: 100%; background: #1a1a1a; border: 1px solid #333; color: white; padding: 12px; border-radius: 10px; margin-bottom: 20px; outline: none; }
        label { color: #7C3AED; font-size: 0.7rem; font-weight: bold; display: block; text-align: left; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="join-card">
        <img src="{{ asset('images/spikia-25.png') }}" class="logo">
        <h2>{{ $sesion->titulo }}</h2>
        <div class="lang-select-box">
            <label>SELECCIONA TU IDIOMA</label>
            <select id="idioma_usuario">
                <option value="es">🇲🇽 Español (Spanish)</option>
                <option value="en">🇺🇸 Inglés (English)</option>
                <option value="pt">🇧🇷 Portugués (Português)</option>
                <option value="fr">🇫🇷 Francés (Français)</option>
                <option value="it">🇮🇹 Italiano (Italiano)</option>
            </select>
        </div>
        <button class="btn-join" onclick="entrar()">UNIRSE AHORA</button>
    </div>

    <script>
        function entrar() {
            const lang = document.getElementById('idioma_usuario').value;
            window.location.href = "{{ route('sesion.transmision', $sesion->slug) }}?lang=" + lang;
        }
    </script>
</body>
</html>