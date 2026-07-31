// Modo 'human_live' — lado del INTERPRETE real (pagina /sesiones/{slug}/interprete).
//
// Lo que SI es funcional hoy: captura la camara del interprete, corre la segmentacion de
// persona/fondo en el navegador (MediaPipe Selfie Segmentation, corriendo 100% local, los
// assets estan self-hosted en /vendor/mediapipe/ en vez de un CDN externo) y compone un
// canvas de salida con el interprete recortado sobre fondo verde (efecto croma tipo Meet).
//
// Lo que NO es funcional todavia: enviar ese video procesado a los oyentes de la sala. Eso
// requiere un proveedor de WebRTC real (LiveKit, Agora, o similar) con cuenta/API keys, que
// todavia no existen en este proyecto. `connectToViewers()` esta deliberadamente sin
// implementar y marcado como tal: conectar un SDK ahi es el siguiente paso cuando se elija
// un proveedor, tomando `outputCanvas` (o su stream via captureStream()) como fuente de video
// a publicar.

import { SelfieSegmentation } from '@mediapipe/selfie_segmentation';

const CHROMA_COLOR = { r: 0, g: 177, b: 64 }; // Verde croma estandar

function init() {
    const config = window.__SPIKIA_INTERPRETE__;
    const sourceVideo = document.getElementById('interprete-source-video');
    const outputCanvas = document.getElementById('interprete-output-canvas');
    const statusEl = document.getElementById('interprete-status');

    if (!config || !config.slug || !sourceVideo || !outputCanvas) {
        return;
    }

    const ctx = outputCanvas.getContext('2d');
    let running = false;

    function setStatus(text) {
        if (statusEl) {
            statusEl.textContent = text;
        }
    }

    function resizeCanvas() {
        outputCanvas.width = sourceVideo.videoWidth || 640;
        outputCanvas.height = sourceVideo.videoHeight || 480;
    }

    function onSegmentationResults(results) {
        if (!running) {
            return;
        }

        resizeCanvas();
        const { width, height } = outputCanvas;

        ctx.save();
        ctx.clearRect(0, 0, width, height);

        // Fondo croma solido.
        ctx.fillStyle = `rgb(${CHROMA_COLOR.r}, ${CHROMA_COLOR.g}, ${CHROMA_COLOR.b})`;
        ctx.fillRect(0, 0, width, height);

        // La mascara de segmentacion (results.segmentationMask) marca donde esta la persona:
        // se usa como "recorte" para pintar el video original solo en esa zona.
        ctx.drawImage(results.segmentationMask, 0, 0, width, height);
        ctx.globalCompositeOperation = 'source-in';
        ctx.drawImage(results.image, 0, 0, width, height);
        ctx.globalCompositeOperation = 'source-over';

        ctx.restore();
    }

    async function startCapture() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { width: 1280, height: 720, facingMode: 'user' },
                audio: true,
            });
            sourceVideo.srcObject = stream;
            await sourceVideo.play();
        } catch (error) {
            setStatus('No se pudo acceder a la camara/microfono: ' + error.message);
            console.error('avatar-interprete: getUserMedia fallo:', error);
            return false;
        }
        return true;
    }

    function startSegmentationLoop() {
        const selfieSegmentation = new SelfieSegmentation({
            // Self-hosted a proposito (ver comentario de cabecera): sin esto, MediaPipe
            // intenta bajar los binarios desde un CDN publico en cada carga.
            locateFile: (file) => `/vendor/mediapipe/selfie_segmentation/${file}`,
        });
        selfieSegmentation.setOptions({ modelSelection: 1 });
        selfieSegmentation.onResults(onSegmentationResults);

        running = true;

        async function frameLoop() {
            if (!running) {
                return;
            }
            if (sourceVideo.readyState >= 2) {
                await selfieSegmentation.send({ image: sourceVideo });
            }
            requestAnimationFrame(frameLoop);
        }

        requestAnimationFrame(frameLoop);
    }

    // --- PENDIENTE: conectar con un proveedor real de WebRTC ---
    // outputCanvas.captureStream(30) da un MediaStream real y funcional del video ya
    // procesado (croma incluido); lo que falta es publicarlo hacia LiveKit/Agora/etc. para
    // que los oyentes de `config.slug` lo reciban. Sin esto, el interprete se ve a si mismo
    // pero nadie mas recibe la señal todavia.
    function connectToViewers() {
        setStatus('Vista previa local activa. Envio en vivo a los oyentes: pendiente de conectar un proveedor WebRTC (LiveKit/Agora).');
    }

    (async () => {
        setStatus('Solicitando permiso de camara...');
        const ok = await startCapture();
        if (!ok) {
            return;
        }
        setStatus('Procesando video (segmentacion de fondo)...');
        startSegmentationLoop();
        connectToViewers();
    })();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
