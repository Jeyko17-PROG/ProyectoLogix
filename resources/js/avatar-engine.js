// Avatar 3D en Lengua de Señas — motor de renderizado (modulo opcional).
//
// Este archivo SOLO se carga cuando el bloque @if en transmision.blade.php es verdadero
// (config('spikia.features.sign_avatar') && $sesion->has_sign_avatar). Si el modulo esta
// apagado, Vite/Blade nunca inyecta este script y el navegador del oyente no gasta ni un
// byte ni un ciclo en el, por diseño.
//
// Se suscribe al mismo canal que listener.js pero a un evento propio e independiente
// (SignLanguageBroadcast, emitido por ProcessSignGlossesJob) para no interferir jamas con
// el pipeline de traduccion por voz/texto.
//
// Estado actual: dibuja la secuencia de glosas recibidas sobre el <canvas> 2D como
// placeholder visual. El punto de extension para el motor real (three.js/WebGL con un
// modelo 3D rigged que reproduzca cada glosa) es reemplazar `renderGlosses()` por la
// inicializacion de la escena 3D y la animacion por glosa; el resto del cableado
// (suscripcion Echo, cola de glosas, ciclo de vida) ya queda resuelto aqui.

import { getEcho } from './echo';

function init() {
    const config = window.__SPIKIA_LISTENER__;
    const canvas = document.getElementById('avatar-canvas');

    if (!config || !config.slug || !canvas) {
        return;
    }

    const ctx = canvas.getContext('2d');
    const glossQueue = [];
    let currentGloss = '';

    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        canvas.width = Math.max(1, Math.round(rect.width));
        canvas.height = Math.max(1, Math.round(rect.height));
    }

    function renderGlosses() {
        if (!ctx) {
            return;
        }

        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = 'rgba(9, 9, 11, 0.9)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        ctx.fillStyle = '#67e8f9';
        ctx.font = 'bold 16px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(currentGloss || '· · ·', canvas.width / 2, canvas.height / 2);
    }

    function playNextGloss() {
        if (glossQueue.length === 0) {
            currentGloss = '';
            renderGlosses();
            return;
        }

        currentGloss = glossQueue.shift();
        renderGlosses();
        window.setTimeout(playNextGloss, 700);
    }

    function handleSignBroadcast(payload) {
        const glosses = Array.isArray(payload?.glosses) ? payload.glosses : [];
        if (glosses.length === 0) {
            return;
        }

        const wasEmpty = glossQueue.length === 0 && currentGloss === '';
        glossQueue.push(...glosses);

        if (wasEmpty) {
            playNextGloss();
        }
    }

    function subscribeEcho() {
        const echo = getEcho();
        if (!echo) {
            // Sin Echo/Pusher configurado el avatar simplemente se queda inactivo: no hay
            // fallback de polling a proposito, para no sumar carga extra al servidor por un
            // modulo que ya de por si es opcional.
            return;
        }

        try {
            echo.channel(`transmision.${config.slug}`)
                .listen('.SignLanguageBroadcast', (payload) => handleSignBroadcast(payload));
        } catch (error) {
            console.warn('avatar-engine: no se pudo suscribir a Echo:', error);
        }
    }

    resizeCanvas();
    renderGlosses();
    window.addEventListener('resize', resizeCanvas);
    subscribeEcho();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
