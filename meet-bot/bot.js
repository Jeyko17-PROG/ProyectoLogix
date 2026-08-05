// Logica del "bot de reunion": entra a un Google Meet como invitado, espera a que el
// anfitrion lo admita, y captura el audio de la pestaña en segmentos que se suben a Laravel.
//
// IMPORTANTE - fragilidad conocida: Google Meet no tiene una API oficial para esto. Todo lo
// de aca abajo depende de la estructura HTML/aria-label actual de Meet, que Google cambia sin
// aviso. Si el bot deja de poder unirse, lo primero a revisar son los selectores de
// findVisibleByText()/NAME_INPUT_SELECTORS/JOIN_BUTTON_TEXTS/IN_CALL_SELECTORS de abajo,
// probando manualmente en un Meet real que texto/aria-label tienen esos elementos hoy.

const fs = require('fs');
const path = require('path');
const axios = require('axios');
const FormData = require('form-data');
const { launch, getStream } = require('puppeteer-stream');

// puppeteer-stream usa puppeteer-core por debajo, que a diferencia del paquete "puppeteer"
// completo NO trae un Chromium propio ni lo detecta solo via variables de entorno: hay que
// pasarle executablePath explicitamente en launch() o falla con "An `executablePath` or
// `channel` must be specified for `puppeteer-core`". Se resuelve una vez al arrancar,
// probando PUPPETEER_EXECUTABLE_PATH y despues las rutas tipicas de Windows/Linux.
function resolveChromePath() {
    const candidates = [
        process.env.PUPPETEER_EXECUTABLE_PATH,
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        '/usr/bin/google-chrome-stable',
        '/usr/bin/google-chrome',
    ].filter(Boolean);

    const found = candidates.find((candidate) => {
        try {
            return fs.existsSync(candidate);
        } catch (error) {
            return false;
        }
    });

    if (!found) {
        throw new Error(
            'No se encontro Chrome instalado. Instala Google Chrome o define PUPPETEER_EXECUTABLE_PATH.'
        );
    }
    return found;
}

const CHROME_PATH = resolveChromePath();

const SEGMENT_MS = 5000; // mismo criterio que master.js (SEGMENT_MS) del lado de Laravel.
const JOIN_TIMEOUT_MS = 5 * 60 * 1000; // el anfitrion admite manualmente, puede tardar.
const BOT_DISPLAY_NAME = 'Spikia (traduciendo en vivo)';

// Textos/aria-labels en varios idiomas: Meet elige el idioma segun la cuenta/region, y como
// el bot entra sin sesion de Google no hay forma de forzarlo de antemano.
const JOIN_BUTTON_TEXTS = [
    'pedir unirse', 'solicitar unirse', 'unirse ahora',
    'ask to join', 'join now',
];
const IN_CALL_TEXTS = [
    'salir de la llamada', 'abandonar llamada',
    'leave call', 'leave meeting',
];
const MUTE_MIC_TEXTS = ['desactivar micr', 'turn off microphone', 'mute'];
const MUTE_CAM_TEXTS = ['desactivar cámara', 'turn off camera'];
// Meet muestra esto cuando el enlace no corresponde a una reunion activa en este momento
// (nadie la tiene abierta, o es invalida/vencida) - no es un problema de selectores.
const NOT_JOINABLE_TEXTS = ['no puedes unirte a esta videollamada', "you can't join this video call"];

/** slug -> { page, browser, status, stopRequested } */
const sessions = new Map();

function log(slug, ...args) {
    console.log(`[meet-bot:${slug}]`, ...args);
}

// Temporal, para diagnosticar por que Meet no deja pasar al bot: guarda una captura de
// pantalla + la lista de botones visibles de la pagina en meet-bot/debug/. Se puede quitar
// una vez que el flujo de union quede estable.
async function debugSnapshot(page, slug, label) {
    try {
        const dir = path.join(__dirname, 'debug');
        if (!fs.existsSync(dir)) fs.mkdirSync(dir);
        const stamp = Date.now();
        const base = path.join(dir, `${slug}-${label}-${stamp}`);

        await page.screenshot({ path: `${base}.png` });

        const buttons = await page.evaluate(() => {
            const nodes = Array.from(document.querySelectorAll('button, [role="button"], input'));
            return nodes
                .filter((el) => el.offsetParent !== null)
                .map((el) => ({
                    tag: el.tagName,
                    aria: el.getAttribute('aria-label'),
                    text: (el.textContent || '').trim().slice(0, 60),
                    type: el.getAttribute('type'),
                }))
                .slice(0, 60);
        });
        fs.writeFileSync(`${base}.json`, JSON.stringify(buttons, null, 2));
        console.log(`[meet-bot:${slug}] Debug guardado: ${base}.png / .json`);
    } catch (error) {
        console.log(`[meet-bot:${slug}] No se pudo guardar el debug:`, error.message);
    }
}

/**
 * Busca, entre botones/elementos con role=button, el primero visible cuyo texto o
 * aria-label contenga alguno de los strings dados (case-insensitive). Devuelve un
 * ElementHandle o null.
 */
async function findVisibleByText(page, candidates) {
    const handle = await page.evaluateHandle((candidates) => {
        const nodes = Array.from(document.querySelectorAll('button, [role="button"]'));
        const norm = (s) => (s || '').toLowerCase();
        return nodes.find((el) => {
            const label = norm(el.getAttribute('aria-label')) + ' ' + norm(el.textContent);
            const visible = el.offsetParent !== null;
            return visible && candidates.some((c) => label.includes(c));
        }) || null;
    }, candidates);

    const element = handle.asElement();
    if (!element) {
        await handle.dispose();
        return null;
    }
    return element;
}

/**
 * Reintenta findVisibleByText() cada intervalMs hasta timeoutMs. Meet tarda un rato en
 * terminar de renderizar la pantalla previa a unirse ("Preparando la llamada...") despues de
 * que page.goto() ya resolvio (networkidle2 no espera a ese render, que es JS del lado del
 * cliente) - sin este retry, buscar el input de nombre o el boton de unirse un instante
 * demasiado pronto siempre da "no encontrado" aunque la pagina este perfectamente bien.
 */
async function waitForVisibleByText(page, candidates, timeoutMs = 30000, intervalMs = 1000) {
    const deadline = Date.now() + timeoutMs;
    while (Date.now() < deadline) {
        const found = await findVisibleByText(page, candidates);
        if (found) return found;
        await new Promise((resolve) => setTimeout(resolve, intervalMs));
    }
    return null;
}

async function waitForNameInput(page, timeoutMs = 30000, intervalMs = 1000) {
    const deadline = Date.now() + timeoutMs;
    while (Date.now() < deadline) {
        const handle = await page.evaluateHandle(() => {
            const inputs = Array.from(document.querySelectorAll('input[type="text"]'));
            return inputs.find((el) => el.offsetParent !== null) || null;
        });
        const element = handle.asElement();
        if (element) return element;
        await handle.dispose();
        await new Promise((resolve) => setTimeout(resolve, intervalMs));
    }
    return null;
}

async function fillDisplayName(page) {
    // El input de nombre en la pantalla previa a unirse no tiene un selector estable propio;
    // se identifica por ser el primer <input type="text"> visible de la pagina.
    const element = await waitForNameInput(page);
    if (!element) {
        return; // Puede ser normal: si el bot ya tiene sesion/permiso previo, Meet a veces se
                 // salta la pantalla de nombre y va directo al boton de unirse.
    }
    await element.click({ clickCount: 3 });
    await element.type(BOT_DISPLAY_NAME, { delay: 20 });
}

async function waitForAdmission(page, slug) {
    const deadline = Date.now() + JOIN_TIMEOUT_MS;
    while (Date.now() < deadline) {
        const inCall = await findVisibleByText(page, IN_CALL_TEXTS);
        if (inCall) {
            return true;
        }
        await new Promise((resolve) => setTimeout(resolve, 2000));
    }
    log(slug, 'Timeout esperando admision del anfitrion.');
    return false;
}

async function bestEffortMute(page) {
    for (const texts of [MUTE_MIC_TEXTS, MUTE_CAM_TEXTS]) {
        try {
            const btn = await findVisibleByText(page, texts);
            if (btn) await btn.click();
        } catch (error) {
            // No es critico: sin dispositivos reales de camara/microfono en este navegador,
            // Meet ya deberia entrar sin enviar audio/video de todos modos.
        }
    }
}

async function uploadChunk(session, buffer) {
    if (!buffer || buffer.length === 0) return;
    const form = new FormData();
    form.append('audio', buffer, { filename: 'segment.webm', contentType: 'audio/webm' });

    try {
        await axios.post(session.ingestUrl, form, {
            headers: {
                ...form.getHeaders(),
                'X-Spikia-Bot-Token': session.ingestToken,
            },
            maxBodyLength: 25 * 1024 * 1024,
            timeout: 15000,
        });
    } catch (error) {
        log(session.slug, 'Fallo al subir un segmento de audio a Laravel:', error.message);
    }
}

async function captureLoop(session) {
    while (session.status === 'active' && !session.stopRequested) {
        let stream;
        try {
            stream = await getStream(session.page, { audio: true, video: false, mimeType: 'audio/webm' });
        } catch (error) {
            log(session.slug, 'No se pudo iniciar la captura de audio:', error.message);
            break;
        }

        const chunks = [];
        stream.on('data', (chunk) => chunks.push(chunk));

        await new Promise((resolve) => setTimeout(resolve, SEGMENT_MS));

        try {
            await stream.destroy();
        } catch (error) {
            log(session.slug, 'Aviso cerrando el stream de audio del segmento:', error.message);
        }

        await uploadChunk(session, Buffer.concat(chunks));
    }
}

async function join({ slug, meetUrl, ingestUrl, ingestToken }) {
    const existing = sessions.get(slug);
    if (existing && ['joining', 'active'].includes(existing.status)) {
        return { status: existing.status };
    }

    const session = { slug, ingestUrl, ingestToken, status: 'joining', stopRequested: false };
    sessions.set(slug, session);

    (async () => {
        try {
            const browser = await launch({
                headless: 'new',
                executablePath: CHROME_PATH,
                args: [
                    '--use-fake-ui-for-media-stream', // nunca deja que Chrome pida permiso de camara/mic
                    '--no-sandbox',
                ],
            });
            session.browser = browser;

            const page = await browser.newPage();
            session.page = page;
            await page.setViewport({ width: 1280, height: 720 });

            log(slug, 'Abriendo', meetUrl);
            await page.goto(meetUrl, { waitUntil: 'networkidle2', timeout: 60000 });
            await debugSnapshot(page, slug, 'recien-cargada');

            await fillDisplayName(page);
            await bestEffortMute(page);

            const joinBtn = await waitForVisibleByText(page, JOIN_BUTTON_TEXTS, 30000);
            if (!joinBtn) {
                await debugSnapshot(page, slug, 'sin-boton-join');

                const notJoinable = await page.evaluate((texts) => {
                    const body = document.body.innerText.toLowerCase();
                    return texts.some((t) => body.includes(t));
                }, NOT_JOINABLE_TEXTS).catch(() => false);

                if (notJoinable) {
                    throw new Error('La reunion no esta activa ahora mismo (nadie la tiene abierta) o el enlace no es valido.');
                }
                throw new Error('No se encontro el boton para pedir unirse a la reunion.');
            }
            await joinBtn.click();

            const admitted = await waitForAdmission(page, slug);
            if (!admitted || session.stopRequested) {
                throw new Error('El anfitrion no admitio al bot a tiempo.');
            }

            session.status = 'active';
            log(slug, 'Admitido, capturando audio.');
            await captureLoop(session);
        } catch (error) {
            log(slug, 'Error uniendose a la reunion:', error.message);
            // OJO: a proposito NO se llama a leave() aca - leave() borra la sesion del mapa,
            // y eso perderia el status 'error' al instante (el endpoint de status volveria a
            // reportar 'idle', escondiendo el fallo). Solo se cierra el navegador; la entrada
            // se mantiene hasta que Laravel pida explicitamente /leave o se intente un nuevo /join.
            session.status = 'error';
            session.lastError = error.message;
            try {
                if (session.browser) await session.browser.close();
            } catch (closeError) {
                log(slug, 'Aviso cerrando el navegador tras error:', closeError.message);
            }
        }
    })();

    return { status: 'joining' };
}

async function leave({ slug }) {
    const session = sessions.get(slug);
    if (!session) {
        return { status: 'idle' };
    }

    session.stopRequested = true;
    session.status = 'stopped';

    try {
        if (session.browser) await session.browser.close();
    } catch (error) {
        log(slug, 'Aviso cerrando el navegador:', error.message);
    }

    sessions.delete(slug);
    return { status: 'stopped' };
}

function statusOf(slug) {
    const session = sessions.get(slug);
    if (!session) return { status: 'idle' };
    return { status: session.status, error: session.lastError || null };
}

module.exports = { join, leave, statusOf };
