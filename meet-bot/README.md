# spikia-meet-bot

Worker Node.js separado de Laravel. Entra a una reunión de Google Meet como invitado (usando
un navegador Chrome automatizado) y sube el audio capturado al endpoint de ingesta de Spikia
(`POST /sesiones/{slug}/bot-audio`).

No corre dentro de PHP: necesita un proceso persistente con Chrome instalado, algo que la
mayoría de hosting compartido no permite. Está pensado para correr en el VPS con acceso SSH.

## Instalación en el VPS

```bash
# Dependencias de sistema para Chrome headless (Debian/Ubuntu)
sudo apt-get update && sudo apt-get install -y \
    libnss3 libatk1.0-0 libatk-bridge2.0-0 libgbm1 libasound2 \
    libxkbcommon0 libxcomposite1 libxdamage1 libxrandr2 libxfixes3 libpango-1.0-0

cd meet-bot
npm ci
```

## Variables de entorno

- `PORT` (opcional, default `4100`) — puerto donde escucha la API interna.
- `SPIKIA_MEETBOT_SECRET` — mismo valor que `SPIKIA_MEETBOT_SECRET` en el `.env` de Laravel.

## Arrancar el proceso (con PM2)

```bash
npm install -g pm2
SPIKIA_MEETBOT_SECRET=el-mismo-secreto-que-en-laravel pm2 start index.js --name spikia-meet-bot
pm2 save
```

El puerto (`127.0.0.1:4100` por defecto, ya viene atado a localhost en `index.js`) **no debe
exponerse públicamente** — solo Laravel necesita alcanzarlo.

## Advertencias

- Google Meet no tiene una API oficial para esto: se automatiza el navegador. Los selectores
  usados en `bot.js` (botón "Pedir unirse", detección de admisión, etc.) dependen del HTML
  actual de Meet y **pueden romperse sin aviso** cuando Google cambia su interfaz. Si el bot
  deja de poder unirse, ese es el primer lugar a revisar.
- El anfitrión de la reunión debe admitir manualmente al bot ("Spikia (traduciendo en vivo)")
  cada vez, igual que a cualquier otro invitado nuevo.
- El bot nunca envía audio/video propio a la llamada (Chrome arranca con
  `--use-fake-ui-for-media-stream` y sin dispositivos reales de cámara/micrófono).
