import { io } from 'socket.io-client';

const config = window.__SPIKIA_LISTENER__;

if (config) {
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('subtitles-container');
        const timelineList = document.getElementById('timeline-list');
        const timelineMeta = document.getElementById('timeline-meta');
        const pendingCount = document.getElementById('pending-count');
        const selectedLanguageLabel = document.getElementById('selected-language-label');
        const langBtns = document.querySelectorAll('.mobile-lang-btn');
        const statusDot = document.getElementById('status-dot');
        const statusText = document.getElementById('status-text');
        const audioBtn = document.getElementById('toggle-audio-btn');
        const audioBtnBg = document.getElementById('audio-btn-bg');
        const iconAudioOn = document.getElementById('icon-audio-on');
        const iconAudioOff = document.getElementById('icon-audio-off');
        const seenMessages = new Set();
        const seenMessageKeys = new Set();
        const languageLabels = config.languageLabels || {};
        const pageLoadedAt = Date.now();
        let initialSyncDone = false;
        let lastRenderedSignature = '';
        let lastRenderedAt = 0;
        const languageMap = {
            en: { lang: 'en', masterLang: 'en-US', base: 'en', speech: 'en-US', name: 'English' },
            'es-ES': { lang: 'es-ES', base: 'es', speech: 'es-ES', name: 'EspaÃ±ol EspaÃ±a' },
            'es-419': { lang: 'es-419', masterLang: 'es-419', base: 'es', speech: 'es-MX', name: 'EspaÃ±ol LatAm' },
            pt: { lang: 'pt', masterLang: 'pt-BR', base: 'pt', speech: 'pt-BR', name: 'PortuguÃªs' },
            it: { lang: 'it', masterLang: 'it-IT', base: 'it', speech: 'it-IT', name: 'Italiano' },
            fr: { lang: 'fr', masterLang: 'fr-FR', base: 'fr', speech: 'fr-FR', name: 'FrancÃ©s' },
        };

        if (!container || !audioBtn || !statusDot || !statusText) {
            return;
        }

        let myLang = localStorage.getItem('spikia_mobile_lang') || config.defaultLang || 'es-ES';
        let audioEnabled = localStorage.getItem('spikia_audio_enabled') === 'true';
        let polling = false;
        const socket = config.socketUrl ? io(config.socketUrl, { transports: ['websocket', 'polling'] }) : null;

        function clearVisibleContent() {
            container.innerHTML = '<p id="placeholder" class="text-zinc-600 font-light italic text-lg animate-pulse tracking-wide">Selecciona tu idioma arriba...</p>';
            if (timelineList) {
                timelineList.innerHTML = `
                    <div class="rounded-2xl border border-dashed border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-500">
                        Los mensajes traducidos aparecera aqui despues del retardo minimo de 3 segundos.
                    </div>
                `;
            }
        }

        async function readJsonResponse(response, fallbackMessage) {
            const contentType = response.headers.get('content-type') || '';
            const bodyText = await response.text();
            const cleanedText = bodyText
                .replace(/^\uFEFF+/, '')
                .replace(/[\uFEFF\u200B\u200C\u200D]/g, '')
                .trim();

            if (contentType.includes('application/json') || cleanedText.startsWith('{') || cleanedText.startsWith('[')) {
                try {
                    return JSON.parse(cleanedText);
                } catch (error) {
                    const jsonStart = cleanedText.search(/[\[{]/);
                    const jsonEnd = Math.max(cleanedText.lastIndexOf('}'), cleanedText.lastIndexOf(']'));

                    if (jsonStart !== -1 && jsonEnd !== -1 && jsonEnd > jsonStart) {
                        const sliced = cleanedText.slice(jsonStart, jsonEnd + 1);
                        try {
                            return JSON.parse(sliced);
                        } catch (innerError) {
                            //
                        }
                    }

                    throw new Error(fallbackMessage || 'La respuesta del servidor no es JSON valida.');
                }
            }

            const stripped = cleanedText.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
            throw new Error(stripped || fallbackMessage || 'La respuesta del servidor no es JSON valida.');
        }

        function normalizeMessage(message = {}) {
            const texto = collapseAdjacentRepeatedWords(message.texto || message.traduccion || '');
            const idioma = message.idioma || 'es';
            const variante = message.variante || '';
            const publishedAt = Number(message.published_at || 0);
            const availableAt = Number(message.available_at || 0);
            const id = message.id || `${idioma}:${variante}:${publishedAt || Date.now()}:${texto}`;
            const dedupeKey = `${idioma}|${variante}|${availableAt || publishedAt || 0}|${texto}`;

            return {
                ...message,
                id,
                dedupeKey,
                texto,
                idioma,
                variante,
                genero: message.genero || message.gender || '',
            };
        }

        function collapseAdjacentRepeatedWords(text) {
            return String(text || '')
                .trim()
                .replace(/\s+/g, ' ')
                .replace(/\b(\w+)(?:\s+\1\b)+/gi, '$1');
        }

        function updateAudioUI() {
            if (iconAudioOn) iconAudioOn.classList.toggle('hidden', !audioEnabled);
            if (iconAudioOff) iconAudioOff.classList.toggle('hidden', audioEnabled);
            if (audioBtnBg) audioBtnBg.classList.toggle('opacity-100', audioEnabled);
        }

        function activarBoton(lang) {
            langBtns.forEach((b) => {
                b.classList.remove('active-lang');
                b.classList.remove('border-cyan-400', 'bg-cyan-500/10', 'text-cyan-200', 'ring-1', 'ring-cyan-400/40');
                if (b.dataset.lang === lang) b.classList.add('active-lang');
                if (b.dataset.lang === lang) b.classList.add('border-cyan-400', 'bg-cyan-500/10', 'text-cyan-200', 'ring-1', 'ring-cyan-400/40');
            });
        }

        function emitLanguageSelection(payload) {
            if (!socket) return;

            socket.emit('active-language-changed', {
                origin: 'listener',
                ...payload,
            });
        }

        function emitAudioState(active) {
            if (!socket) return;

            socket.emit('listener-audio-state', {
                origin: 'listener',
                active: !!active,
                lang: myLang,
            });
        }

        function applyLanguageSelection(lang, shouldEmit = true) {
            const details = languageMap[lang] || {
                lang,
                masterLang: lang,
                base: lang.split('-')[0] || lang,
                speech: lang,
                name: lang.toUpperCase(),
            };
            const prettyName = languageLabels[details.lang] || details.name || details.lang.toUpperCase();

            myLang = details.lang;
            localStorage.setItem('spikia_mobile_lang', details.lang);
            localStorage.setItem('spikia_selected_listener_lang', details.lang);
            activarBoton(myLang);
            if (selectedLanguageLabel) {
                selectedLanguageLabel.textContent = `${prettyName} · ${details.lang.toUpperCase()}`;
            }
            seenMessages.clear();
            seenMessageKeys.clear();
            clearVisibleContent();

            if (shouldEmit) {
                emitLanguageSelection({ ...details, name: prettyName });
            }

            pollMessages();
        }

        function hablarTexto(texto, lang, variante) {
            window.speechSynthesis.cancel();
            const ut = new SpeechSynthesisUtterance(texto);
            const voiceMap = {
                en: 'en-US',
                es: variante === 'es-419' ? 'es-MX' : 'es-ES',
                'es-ES': 'es-ES',
                'es-419': 'es-MX',
                pt: 'pt-BR',
                it: 'it-IT',
                fr: 'fr-FR',
            };
            ut.lang = voiceMap[variante || lang] || 'es-ES';
            ut.rate = 1.0;
            const gender = String(window.__SPIKIA_LAST_GENDER__ || '').toLowerCase();
            ut.pitch = gender === 'male' ? 0.85 : 1.15;

            const voices = window.speechSynthesis.getVoices();
            const matchingVoices = voices.filter((v) => v.lang && v.lang.includes(ut.lang));
            const preferredVoice = matchingVoices.find((voice) => {
                const name = String(voice.name || '').toLowerCase();
                if (gender === 'male') {
                    return /male|man|hombre|mascul/i.test(name);
                }
                if (gender === 'female') {
                    return /female|woman|mujer|feminin/i.test(name);
                }
                return false;
            });
            const voice = preferredVoice || matchingVoices[0];
            if (voice) ut.voice = voice;
            ut.onend = () => emitAudioState(false);
            ut.onerror = () => emitAudioState(false);
            emitAudioState(true);
            window.speechSynthesis.speak(ut);
        }

        function actualizarSubtitulos(texto) {
            const placeholder = document.getElementById('placeholder');
            if (placeholder) placeholder.remove();

            container.innerHTML = '';

            const p = document.createElement('p');
            p.className = 'text-3xl font-black text-white animate-subtitle-in uppercase italic mb-4';
            p.innerText = collapseAdjacentRepeatedWords(texto);
            container.appendChild(p);
        }

        function renderTimelineItem(message) {
            if (!timelineList) return;

            const normalizedText = collapseAdjacentRepeatedWords(message.texto || '');
            const signature = `${message.idioma || ''}|${message.variante || ''}|${normalizedText}`;
            if (signature === lastRenderedSignature && (Date.now() - lastRenderedAt) < 8000) {
                return;
            }

            lastRenderedSignature = signature;
            lastRenderedAt = Date.now();

            const item = document.createElement('article');
            item.className = 'rounded-2xl border border-white/10 bg-white/5 px-4 py-3 flex items-start justify-between gap-4';

            const langLabel = (message.variante || message.idioma || 'es').toString().toUpperCase();
            const stamp = message.published_at ? new Date(message.published_at * 1000) : new Date();

            item.innerHTML = `
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="inline-flex items-center rounded-full border border-neonBlue/30 bg-neonBlue/10 px-2 py-1 text-[9px] font-black uppercase tracking-[0.3em] text-neonBlue">${langLabel}</span>
                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-zinc-500">Liberado +3s</span>
                    </div>
                    <p class="text-sm text-white/90 leading-6 break-words">${normalizedText}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-[10px] font-black uppercase tracking-[0.25em] text-zinc-500">${stamp.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })}</p>
                </div>
            `;

            timelineList.prepend(item);
            while (timelineList.children.length > 6) {
                timelineList.removeChild(timelineList.lastElementChild);
            }
        }

        function queueMessageDisplay(message) {
            if (message.dedupeKey && seenMessageKeys.has(message.dedupeKey)) return;
            
            if (message.dedupeKey) seenMessageKeys.add(message.dedupeKey);
            window.__SPIKIA_LAST_GENDER__ = message.genero || window.__SPIKIA_LAST_GENDER__ || '';

            const displaySignature = `${message.idioma || ''}|${message.variante || ''}|${collapseAdjacentRepeatedWords(message.texto || '')}`;
            if (displaySignature === lastRenderedSignature && (Date.now() - lastRenderedAt) < 8000) {
                return;
            }

            const rawAvailableAt = Number(message.available_at || 0);
            const availableAtMs = rawAvailableAt > 0
                ? (rawAvailableAt < 100000000000 ? rawAvailableAt * 1000 : rawAvailableAt)
                : 0;
            const wait = availableAtMs > 0 ? Math.max(0, availableAtMs - Date.now()) : 3000;

            setTimeout(() => {
                lastRenderedSignature = displaySignature;
                lastRenderedAt = Date.now();
                actualizarSubtitulos(message.texto);
                renderTimelineItem(message);

                if (audioEnabled) {
                    hablarTexto(collapseAdjacentRepeatedWords(message.texto), message.idioma, message.variante);
                }
            }, wait);
        }

        function handleIncomingMessage(message, force = false) {
            if (!message) return;
            if (message.tipo === 'original') return;

            const normalized = normalizeMessage(message);
            const publishedAtMs = Number(normalized.published_at || 0) * 1000;
            if (!force && publishedAtMs && publishedAtMs < pageLoadedAt) return;
            if (!normalized.id || seenMessages.has(normalized.id)) return;
            if (!matchesLanguage(normalized)) return;

            seenMessages.add(normalized.id);
            queueMessageDisplay(normalized);
        }

        function isRecentMessage(message) {
            const publishedAtMs = Number(message?.published_at || 0) * 1000;
            if (!publishedAtMs) return false;
            return (Date.now() - publishedAtMs) <= 30000;
        }

        function syncLatestVisibleMessage(messages = []) {
            if (initialSyncDone) return;

            const visibleMessages = Array.isArray(messages)
                ? messages
                    .filter((message) => message && message.tipo !== 'original')
                    .filter((message) => matchesLanguage(normalizeMessage(message)))
                    .filter((message) => isRecentMessage(message))
                : [];

            if (!visibleMessages.length) {
                initialSyncDone = true;
                return;
            }

            const latest = visibleMessages.sort((a, b) => {
                const aTime = Number(a.available_at || a.published_at || 0);
                const bTime = Number(b.available_at || b.published_at || 0);
                return aTime - bTime;
            }).pop();

            if (latest) {
                handleIncomingMessage(latest, true);
            }

            initialSyncDone = true;
        }

        function setLanguage(lang) {
            applyLanguageSelection(lang, true);
        }

        function setOnline() {
            statusDot.className = 'w-2 h-2 rounded-full bg-cyan-400 shadow-[0_0_10px_#22d3ee]';
            statusText.innerText = 'EN LINEA';
        }

        function setOffline() {
            statusDot.className = 'w-2 h-2 rounded-full bg-red-500 shadow-[0_0_10px_red]';
            statusText.innerText = 'DESCONECTADO';
        }

        function matchesLanguage(data) {
            const lang = data.idioma || 'es';
            const variant = data.variante || '';

            if (myLang === 'es-ES') {
                return lang === 'es' && (!variant || variant === 'es-ES');
            }

            if (myLang === 'es-419') {
                return lang === 'es' && (variant === 'es-419' || !variant);
            }

            return myLang === lang;
        }

        async function pollMessages() {
            if (polling) return;
            polling = true;

            try {
                const response = await fetch(config.feedUrl, {
                    headers: { Accept: 'application/json' },
                });
                const data = await readJsonResponse(response, 'No se pudieron leer los mensajes.');

                if (!response.ok) {
                    throw new Error(data.message || 'No se pudieron leer los mensajes.');
                }

                setOnline();
                if (pendingCount) pendingCount.textContent = `${data.pending_count ?? 0} pendientes`;
                if (timelineMeta) {
                    timelineMeta.textContent = data.next_available_in_seconds !== null
                        ? `El siguiente mensaje se libera en ${data.next_available_in_seconds}s.`
                        : 'Todo al dia. No hay mensajes en espera.';
                }

                const allMessages = Array.isArray(data.messages) ? data.messages : [];
                allMessages.forEach((message) => handleIncomingMessage(message));
                syncLatestVisibleMessage(allMessages);

            } catch (error) {
                console.error('Error consultando la transmision:', error);
                setOffline();
            } finally {
                polling = false;
            }
        }

        function subscribeRealtime() {
            if (!socket) return;

            socket.on('mensaje-congreso', (event) => handleIncomingMessage(event));
        }

        audioBtn.addEventListener('click', () => {
            audioEnabled = !audioEnabled;
            localStorage.setItem('spikia_audio_enabled', audioEnabled);
            updateAudioUI();
            if (audioEnabled) {
                const silent = new SpeechSynthesisUtterance('');
                window.speechSynthesis.speak(silent);
            } else {
                window.speechSynthesis.cancel();
            }
        });

        updateAudioUI();
        activarBoton(myLang);
        setLanguage(myLang);
        subscribeRealtime();
        pollMessages();

        langBtns.forEach((btn) => {
            btn.addEventListener('click', () => setLanguage(btn.dataset.lang));
        });

        window.setInterval(pollMessages, 1000);
    });
}



