// API interna diminuta que Laravel usa para orquestar el bot (ver app/Services/MeetingBotClient.php).
// No debe exponerse publicamente: solo Laravel deberia poder alcanzar este puerto (en Render,
// via un servicio "Private"/red interna; en un VPS propio, dejando el puerto solo accesible
// desde localhost/red privada con el firewall). La autenticacion real de todos modos es el
// secreto compartido de abajo, no la ubicacion de red: escucha en 0.0.0.0 porque cuando este
// worker corre en su propio contenedor (Render, Docker) 127.0.0.1 solo se referiria a si mismo
// y Laravel, en OTRO contenedor, nunca podria alcanzarlo.

const express = require('express');
const bot = require('./bot');

const PORT = process.env.PORT || 4100;
const SECRET = process.env.SPIKIA_MEETBOT_SECRET || '';

const app = express();
app.use(express.json());

app.use((req, res, next) => {
    const auth = req.header('authorization') || '';
    const token = auth.startsWith('Bearer ') ? auth.slice(7) : '';
    if (!SECRET || token !== SECRET) {
        return res.status(401).json({ error: 'unauthorized' });
    }
    next();
});

app.post('/join', async (req, res) => {
    const { slug, meetUrl, ingestUrl, ingestToken } = req.body || {};
    if (!slug || !meetUrl || !ingestUrl || !ingestToken) {
        return res.status(422).json({ error: 'slug, meetUrl, ingestUrl e ingestToken son requeridos' });
    }

    try {
        const result = await bot.join({ slug, meetUrl, ingestUrl, ingestToken });
        res.json(result);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

app.post('/leave', async (req, res) => {
    const { slug } = req.body || {};
    if (!slug) {
        return res.status(422).json({ error: 'slug es requerido' });
    }

    try {
        const result = await bot.leave({ slug });
        res.json(result);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

app.get('/status/:slug', (req, res) => {
    res.json({ status: bot.statusOf(req.params.slug) });
});

app.listen(PORT, '0.0.0.0', () => {
    console.log(`spikia-meet-bot escuchando en 0.0.0.0:${PORT}`);
});
