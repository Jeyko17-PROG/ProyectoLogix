<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Spikia Live</title>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

    <style>
        body { 
            margin: 0; 
            background: #000; 
            color: #fff; 
            font-family: sans-serif; 
            display: flex; 
            flex-direction: column; 
            height: 100vh; 
            justify-content: center; 
            align-items: center; 
        }

        #texto-pantalla { 
            font-size: 2.5rem; 
            text-align: center; 
            width: 85%; 
            font-weight: 300; 
            line-height: 1.4; 
        }

        .status { 
            position: fixed; 
            top: 20px; 
            font-size: 12px; 
            display: flex; 
            align-items: center; 
        }

        #dot { 
            width: 10px; 
            height: 10px; 
            background: red; 
            border-radius: 50%; 
            margin-right: 8px; 
        }
    </style>
</head>
<body>

    <div class="status">
        <div id="dot"></div>
        <span id="st">CONECTANDO...</span>
    </div>

    <div id="texto-pantalla">Esperando traducción...</div>

    <script>
        // Variables seguras desde Laravel (evita que JS se rompa)
        const slug = @json($sesion->slug);
        const lang = @json(request('lang'));

        console.log("Slug:", slug);
        console.log("Lang:", lang);

        // Inicializar Pusher + Echo
        window.Pusher = Pusher;

        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '888c7c8333a9e8610879',
            cluster: 'us2',
            forceTLS: true
        });

        // Canal único por sesión
        const canal = `transmision.${slug}`;
        console.log("Canal conectado:", canal);

        // Escuchar eventos en tiempo real
        window.Echo.channel(canal)
            .listen('TranscripcionCreada', (data) => {
                console.log("🔥 Evento recibido:", data);

                // Mostrar subtítulo (soporta varios nombres por seguridad)
                document.getElementById('texto-pantalla').innerText =
                    data.texto ||
                    data.texto_traducido ||
                    data.message ||
                    "…";

                // Reproducir audio si viene incluido
                if (data.audio_url) {
                    let audio = new Audio(data.audio_url);
                    audio.play().catch(() => {
                        console.log("Audio bloqueado por el navegador");
                    });
                }
            });

        // Estado de conexión
        window.Echo.connector.pusher.connection.bind('connected', () => {
            document.getElementById('dot').style.background = '#00ff00';
            document.getElementById('st').innerText = 'ESCUCHANDO EN VIVO';
        });
    </script>

</body>
</html>

