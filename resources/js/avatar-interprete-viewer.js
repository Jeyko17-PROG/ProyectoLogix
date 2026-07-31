// Modo 'human_live', lado OYENTE — deberia recibir el stream WebRTC del interprete real
// (ver avatar-interprete-broadcaster.js, que corre en /sesiones/{slug}/interprete) y
// reproducirlo en #avatar-video-player.
//
// Todavia no hay ningun proveedor de WebRTC conectado (LiveKit/Agora requieren cuenta/API
// keys que este proyecto no tiene configuradas), asi que esto es un stub: solo muestra un
// aviso claro en vez de un video roto o una pantalla en blanco. El punto de extension es
// reemplazar `connectToRoom()` por la conexion real del SDK elegido, usando `config.slug`
// como nombre de sala/canal.

function init() {
    const config = window.__SPIKIA_LISTENER__;
    const video = document.getElementById('avatar-video-player');
    const statusEl = document.getElementById('avatar-live-status');

    if (!config || !config.slug || !video) {
        return;
    }

    function setStatus(text) {
        if (statusEl) {
            statusEl.textContent = text;
        }
    }

    // --- PENDIENTE: conectar con el mismo proveedor de WebRTC que use el interprete ---
    function connectToRoom() {
        setStatus('Modo intérprete en vivo: todavía no hay un proveedor de video en tiempo real conectado (pendiente LiveKit/Agora).');
    }

    connectToRoom();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
