import { io } from 'socket.io-client';

const config = window.__SPIKIA_MASTER__;

if (config) {
    document.addEventListener('DOMContentLoaded', () => {
        const elements = {
            masterBtn: document.getElementById('master-live-btn'),
            transcriptionBox: document.getElementById('transcription-box'),
            timerElement: document.getElementById('session-timer'),
            selectedLanguageLabel: document.getElementById('selected-language-label'),
            statusDot: document.getElementById('status-dot'),
            statusText: document.getElementById('status-text'),
            btnBg: document.getElementById('btn-bg-active'),
            bars: document.querySelectorAll('.bar'),
            saveMode: document.getElementById('save-mode'),
        };

        if (!elements.masterBtn || !elements.transcriptionBox || !elements.timerElement) {
            return;
        }

        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
        }

        const targetLanguages = Array.isArray(config.targetLanguages) && config.targetLanguages.length
            ? config.targetLanguages
            : (Array.isArray(config.sessionLanguages) && config.sessionLanguages.length
                ? config.sessionLanguages
                : (config.defaultTargets || ['en', 'pt', 'it', 'fr']));
        const relayUrl = config.relayUrl;
        const transcripcionUrl = config.transcripcionUrl;
        const csrfToken = config.csrfToken;
        const socket = config.socketUrl ? io(config.socketUrl, { transports: ['websocket', 'polling'] }) : null;

        let recognition = null;
        let intervals = { timer: null, visualizer: null };
        let state = {
            isLive: false,
            startTime: null,
            lang: 'es-ES',
            langBase: 'es',
            langName: 'EspaÃ±ol EspaÃ±a',
            listenerLang: null,
            externalAudioActive: false,
            gender: 'male',
            saveMode: elements.saveMode ? elements.saveMode.value : 'resumen',
            lastTranscript: '',
            lastTranscriptAt: 0,
            cooldownUntil: 0,
        };

        function formatLanguageLabel(langId) {
            if (!langId) return 'EspaÃ±ol EspaÃ±a';
            return (config.languageLabels && config.languageLabels[langId]) || String(langId).replace('-', ' ').toUpperCase();
        }

        function getLanguageBase(langId) {
            if (!langId) return '';
            return String(langId).split('-')[0].toLowerCase();
        }

        function getActiveListenerLang() {
            return state.listenerLang || localStorage.getItem('spikia_selected_listener_lang') || null;
        }

        function collapseAdjacentRepeatedWords(text) {
            return String(text || '')
                .trim()
                .replace(/\s+/g, ' ')
                .replace(/\b(\w+)(?:\s+\1\b)+/gi, '$1');
        }

        state.langName = formatLanguageLabel(state.lang);

        function highlightLanguageButton(langId) {
            document.querySelectorAll('.language-btn').forEach((btn) => {
                const isActive = btn.getAttribute('data-lang-id') === langId;
                btn.classList.toggle('border-indigo-600', isActive);
                btn.classList.toggle('text-white', isActive);
                btn.classList.toggle('bg-indigo-600/10', isActive);
                btn.classList.toggle('ring-1', isActive);
                btn.classList.toggle('ring-cyan-400/40', isActive);
            });
        }

        function toListenerLang(masterLangId) {
            if (!masterLangId) return 'es-ES';
            if (masterLangId === 'es-419' || masterLangId === 'es-ES') return masterLangId;
            return String(masterLangId).split('-')[0];
        }

        function updateSelectedLanguageLabel(langId) {
            if (elements.selectedLanguageLabel) {
                elements.selectedLanguageLabel.textContent = `${formatLanguageLabel(langId)} · ${(langId || 'es-ES').toUpperCase()}`;
            }
        }

        function stopRecognitionTemporarily(delay = 0) {
            if (!recognition || !state.isLive) return;

            try { recognition.stop(); } catch (e) {}

            if (delay > 0) {
                setTimeout(() => {
                    if (!state.isLive || state.externalAudioActive) return;
                    try { recognition.start(); } catch (e) {}
                }, delay);
            }
        }

        highlightLanguageButton(state.lang);
        updateSelectedLanguageLabel(state.lang);

        function emitLanguageSelection(payload) {
            if (!socket) return;

            socket.emit('active-language-changed', {
                origin: 'master',
                ...payload,
            });
        }

        function applyLanguageSelection(payload, shouldEmit = true) {
            const langId = payload.lang || state.lang;
            const langBase = payload.base || state.langBase;
            const langName = payload.name || state.langName;
            const speechLang = payload.speech || langId;

            state.lang = langId;
            state.langBase = langBase;
            state.langName = langName;
            highlightLanguageButton(langId);
            updateSelectedLanguageLabel(langId);

            if (recognition) {
                recognition.lang = speechLang;

                if (state.isLive) {
                    try { recognition.stop(); } catch (e) {}
                    setTimeout(() => {
                        try { recognition.lang = speechLang; recognition.start(); } catch (e) {}
                    }, 200);
                }
            }

            if (shouldEmit) {
                emitLanguageSelection({
                    lang: langId,
                    listenerLang: toListenerLang(langId),
                    base: langBase,
                    name: langName,
                    speech: speechLang,
                });
            }
        }

        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            recognition.continuous = true;
            recognition.interimResults = false;
            recognition.lang = state.lang;

            recognition.onresult = (event) => {
                let finalTranscript = '';
                for (let i = event.resultIndex; i < event.results.length; ++i) {
                    if (event.results[i].isFinal) finalTranscript += event.results[i][0].transcript;
                }
                const normalizedTranscript = collapseAdjacentRepeatedWords(finalTranscript);
                const now = Date.now();

                if (normalizedTranscript.length > 1 && normalizedTranscript !== state.lastTranscript) {
                    state.lastTranscript = normalizedTranscript;
                    state.lastTranscriptAt = now;
                    state.cooldownUntil = now + 1500;
                    const availableAt = now + 3000;
                    if (state.isLive) {
                        scheduleMasterDisplay(normalizedTranscript, state.langName, availableAt);
                    }
                    enviarTraducciones(normalizedTranscript, availableAt);
                }
            };

            recognition.onend = () => {
                if (state.isLive) {
                    if (Date.now() < state.cooldownUntil) {
                        setTimeout(() => {
                            try { recognition.start(); } catch (e) {}
                        }, Math.max(300, state.cooldownUntil - Date.now()));
                        return;
                    }

                    const elapsed = Date.now() - state.lastTranscriptAt;
                    if (elapsed < 350) {
                        setTimeout(() => {
                            try { recognition.start(); } catch (e) {}
                        }, 350);
                        return;
                    }

                    try { recognition.start(); } catch (e) {}
                }
            };

            recognition.onerror = (e) => {
                if (e.error === 'no-speech') {
                    return;
                }

                console.error('Recognition error:', e.error);
                if (state.isLive && e.error !== 'not-allowed') {
                    const delay = Date.now() < state.cooldownUntil ? Math.max(300, state.cooldownUntil - Date.now()) : 200;
                    setTimeout(() => { try { recognition.start(); } catch (ex) {} }, delay);
                }
            };
        }

        async function publicarMensaje(texto, idioma, variante = '', tipo = 'texto', id = '') {
            try {
                await fetch(relayUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        texto,
                        idioma,
                        variante: variante || '',
                        genero: state.gender,
                        tipo,
                        id,
                    }),
                });
            } catch (e) {
                console.error('Relay error:', e);
            }
        }

        async function guardarTranscripcion(texto, idioma) {
            try {
                await fetch(transcripcionUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        sesion_id: config.sesionId,
                        slug: config.slug,
                        texto,
                        idioma,
                        modo: state.saveMode,
                    }),
                });
            } catch (e) {
                console.warn('Silenced Save Error (500):', e);
            }
        }

        function emitSocketMessage(payload) {
            if (!socket) return;
            socket.emit('mensaje-congreso', payload);
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

        function scheduleMasterDisplay(text, langLabel, availableAt) {
            const delay = Math.max(0, (availableAt || Date.now()) - Date.now());
            setTimeout(() => {
                updateUIBox(text, langLabel);
            }, delay);
        }

        async function enviarTraducciones(texto, availableAt) {
            const idiomaOriginal = state.langBase;
            const varianteOriginal = state.lang;
            const originalId = crypto.randomUUID();
            const targetList = Array.isArray(targetLanguages) && targetLanguages.length
                ? Array.from(new Set(targetLanguages.filter(Boolean)))
                : [];

            publicarMensaje(texto, idiomaOriginal, varianteOriginal, 'original', originalId);
            guardarTranscripcion(texto, state.lang);
            emitSocketMessage({
                id: originalId,
                texto,
                idioma: idiomaOriginal,
                variante: varianteOriginal,
                genero: state.gender,
                tipo: 'original',
                available_at: availableAt,
                published_at: Math.floor(Date.now() / 1000),
            });

            targetList.forEach(async (targetLang) => {
                if (targetLang === state.lang || getLanguageBase(targetLang) === state.langBase) return;

                try {
                    const res = await fetch('/traducciones', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            sesion_id: config.sesionId,
                            texto,
                            idioma: targetLang,
                            variante: '',
                        }),
                    });

                    if (res.ok) {
                        const data = await readJsonResponse(res, 'No se pudo leer la traduccion.');
                        if (data.traduccion) {
                            const rLang = targetLang.split('-')[0];
                            const rVar = targetLang.includes('-') ? targetLang : '';
                            const translationId = crypto.randomUUID();
                            publicarMensaje(data.traduccion, rLang, rVar, 'traduccion', translationId);
                            guardarTranscripcion(data.traduccion, targetLang);
                            emitSocketMessage({
                                id: translationId,
                                texto: data.traduccion,
                                idioma: rLang,
                                variante: rVar,
                                genero: state.gender,
                                tipo: 'traduccion',
                                available_at: availableAt,
                                published_at: Math.floor(Date.now() / 1000),
                            });
                        }
                    } else {
                        console.error('Translation fail (422/500) for ' + targetLang);
                    }
                } catch (e) {
                    console.error('Fetch error:', e);
                }
            });
        }

        function updateUIBox(text, langLabel) {
            const ph = document.getElementById('placeholder-text');
            if (ph) ph.remove();
            const p = document.createElement('div');
            p.className = 'p-6 mb-4 bg-zinc-900/50 border border-white/10 rounded-[2rem] animate-in fade-in slide-in-from-bottom-4 duration-700';
            p.innerHTML = `<span class="text-indigo-500 font-black text-[10px] block mb-2 uppercase tracking-[0.2em]">${langLabel}</span><p class="text-2xl font-light leading-relaxed text-white">${collapseAdjacentRepeatedWords(text)}</p>`;
            elements.transcriptionBox.appendChild(p);
            elements.transcriptionBox.scrollTo({ top: elements.transcriptionBox.scrollHeight, behavior: 'smooth' });
        }

        elements.masterBtn.addEventListener('click', () => {
            state.isLive = !state.isLive;
            if (state.isLive) {
                if (recognition) {
                    recognition.lang = state.lang;
                    try { recognition.start(); } catch (e) {}
                }
                state.startTime = new Date();
                state.lastTranscript = '';
                state.lastTranscriptAt = 0;
                updateSelectedLanguageLabel(state.lang);
                intervals.timer = setInterval(() => {
                    const diff = new Date() - state.startTime;
                    const h = String(Math.floor(diff / 3600000)).padStart(2, '0');
                    const m = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
                    const s = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
                    elements.timerElement.innerText = `${h}:${m}:${s}`;
                }, 1000);
                elements.statusDot.className = 'relative inline-flex rounded-full h-3 w-3 bg-cyan-400 animate-pulse shadow-[0_0_10px_#22d3ee]';
                elements.statusText.innerText = 'LIVE RUNNING';
                if (elements.btnBg) elements.btnBg.style.opacity = '1';
                intervals.visualizer = setInterval(() => {
                    elements.bars.forEach((b) => (b.style.height = `${Math.random() * 60 + 20}%`));
                }, 150);
            } else {
                if (recognition) recognition.stop();
                clearInterval(intervals.timer);
                clearInterval(intervals.visualizer);
                elements.statusDot.className = 'relative inline-flex rounded-full h-3 w-3 bg-zinc-700';
                elements.statusText.innerText = 'SYSTEM STANDBY';
                if (elements.btnBg) elements.btnBg.style.opacity = '0';
                elements.bars.forEach((b) => (b.style.height = '15%'));
            }
        });

        document.querySelectorAll('.language-btn').forEach((btn) => {
            btn.addEventListener('click', function () {
                applyLanguageSelection({
                    lang: this.getAttribute('data-lang-id'),
                    base: this.getAttribute('data-lang-base'),
                    name: this.getAttribute('data-lang-name'),
                    speech: this.getAttribute('data-speech-lang'),
                });
            });
        });

        document.querySelectorAll('.voice-gender-btn').forEach((btn) => {
            btn.addEventListener('click', function () {
                state.gender = this.getAttribute('data-gender');
                document.querySelectorAll('.voice-gender-btn').forEach((b) => b.classList.remove('bg-indigo-600', 'text-white'));
                this.classList.add('bg-indigo-600', 'text-white');
            });
        });

        if (socket) {
            socket.on('active-language-changed', (payload) => {
                if (!payload || payload.origin === 'master') return;
                state.listenerLang = payload.listenerLang || payload.lang || state.listenerLang;
            });

            socket.on('listener-audio-state', (payload) => {
                state.externalAudioActive = !!payload?.active;

                if (state.externalAudioActive) {
                    stopRecognitionTemporarily();
                    return;
                }

                if (state.isLive && recognition) {
                    setTimeout(() => {
                        if (!state.isLive || state.externalAudioActive) return;
                        try { recognition.start(); } catch (e) {}
                    }, 250);
                }
            });
        }
    });
}


