<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SPIKIA MASTER</title>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
    <style>
        :root { --primary: #A855F7; --bg: #0b0b0b; --card: #111; --success: #00ff88; }
        body { background: var(--bg); color: white; font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        
        /* Sidebar Compacto */
        .sidebar { width: 320px; background: #000; border-right: 1px solid #222; padding: 12px; display: flex; flex-direction: column; gap: 10px; }
        .master-btn { background: #000; border: 1px solid #333; padding: 10px; border-radius: 30px; display: flex; align-items: center; justify-content: center; gap: 10px; cursor: pointer; }
        .master-btn.active { border-color: var(--success); }
        .dot { width: 10px; height: 10px; border-radius: 50%; background: #ff4444; }
        .master-btn.active .dot { background: var(--success); box-shadow: 0 0 10px var(--success); }

        .monitor { background: #050505; border: 1px solid #222; border-radius: 10px; padding: 10px; height: 140px; display: flex; flex-direction: column; }
        .monitor label { font-size: 9px; color: var(--primary); font-weight: bold; margin-bottom: 5px; }
        .scroll-area { flex: 1; overflow-y: auto; font-size: 12px; color: #ddd; }
        .msg { padding: 4px 8px; border-left: 2px solid var(--primary); background: rgba(168, 85, 247, 0.1); margin-bottom: 4px; border-radius: 3px; }

        /* Contenido Principal */
        .main { flex: 1; padding: 20px; overflow-y: auto; }
        .lang-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 8px; margin-top: 15px; }
        .lang-card { background: #1a1a1a; border: 1px solid #333; padding: 12px; border-radius: 8px; text-align: center; cursor: pointer; font-size: 11px; font-weight: bold; color: #666; }
        .lang-card.active { border-color: var(--primary); color: white; background: rgba(168, 85, 247, 0.1); }
        
        select { background: #1a1a1a; color: white; border: 1px solid #444; padding: 8px; border-radius: 5px; width: 100%; font-size: 11px; }
        h3 { font-size: 16px; margin: 0; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div style="text-align:center; padding: 10px;"><img src="{{ asset('images/spikia-25.png') }}" style="width:50px"></div>
        <div class="master-btn" id="btn-master" onclick="toggleMaster()">
            <div class="dot"></div>
            <span id="btn-text" style="font-weight:bold; font-size:11px;">SISTEMA EN VIVO</span>
        </div>
        <canvas id="canvas-v" style="height:40px; width:100%"></canvas>
        
        <div class="monitor">
            <label>ENTRADA DE AUDIO (PISO)</label>
            <div id="box-in" class="scroll-area"></div>
        </div>
        <div class="monitor">
            <label>VOZ IA (TRADUCCIÓN)</label>
            <div id="box-out" class="scroll-area"></div>
        </div>
        <div style="margin-top: 5px;">
            <label style="font-size: 9px; color: var(--primary);">VOZ IA SELECCIONADA</label>
            <select id="voice-selection">
                <option value="f1">Voz Femenina 1</option>
                <option value="m1">Voz Masculina 1</option>
            </select>
        </div>
    </div>

    <div class="main">
        <div style="display:flex; gap:15px; margin-bottom:20px; background: #111; padding: 15px; border-radius: 12px;">
            <div style="flex:1">
                <label style="font-size:9px; color:var(--primary); font-weight:bold;">IDIOMA ENTRADA</label>
                <select id="piso-lang"><option value="es-ES">Español</option><option value="en-US">English</option></select>
            </div>
            <div style="flex:1">
                <label style="font-size:9px; color:var(--primary); font-weight:bold;">AUDIO IA (MASTER)</label>
                <select id="master-ia-audio">
                    <option value="off">Silencio (Solo Móviles)</option>
                    <option value="on">Escuchar IA aquí también</option>
                </select>
            </div>
        </div>

        <h3>Canal de Traducción Activo</h3>
        <div class="lang-grid">
            <div class="lang-card active" onclick="setLang(this, 'en')">ENGLISH (US)</div>
            <div class="lang-card" onclick="setLang(this, 'pt')">PORTUGUÊS</div>
            <div class="lang-card" onclick="setLang(this, 'it')">ITALIANO</div>
            <div class="lang-card" onclick="setLang(this, 'fr')">FRANÇAIS</div>
            <div class="lang-card" onclick="setLang(this, 'es')">ESPAÑOL</div>
        </div>
    </div>

    <script>
        let rec, isLive = false, targetLang = 'en', audioCtx, analyser, dataArray;

        async function initVisualizer() {
            if (audioCtx) return;
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const source = audioCtx.createMediaStreamSource(stream);
            analyser = audioCtx.createAnalyser();
            analyser.fftSize = 64;
            source.connect(analyser);
            dataArray = new Uint8Array(analyser.frequencyBinCount);
            draw();
        }

        function draw() {
            requestAnimationFrame(draw);
            const canvas = document.getElementById('canvas-v');
            const ctx = canvas.getContext('2d');
            canvas.width = canvas.offsetWidth;
            analyser.getByteFrequencyData(dataArray);
            ctx.clearRect(0,0, canvas.width, canvas.height);
            let x = 0;
            dataArray.forEach(v => {
                let h = (v/255) * canvas.height;
                ctx.fillStyle = isLive ? '#A855F7' : '#333';
                ctx.fillRect(x, (canvas.height - h)/2, 3, h + 2);
                x += 6;
            });
        }

        async function toggleMaster() {
            isLive = !isLive;
            document.getElementById('btn-master').classList.toggle('active', isLive);
            document.getElementById('btn-text').innerText = isLive ? "DETENER" : "SISTEMA EN VIVO";
            if(isLive) { await initVisualizer(); startRec(); } else if(rec) rec.stop();
        }

        function startRec() {
            const Speech = window.SpeechRecognition || window.webkitSpeechRecognition;
            rec = new Speech();
            rec.lang = document.getElementById('piso-lang').value;
            rec.continuous = false;
            rec.onresult = async (e) => {
                const text = e.results[0][0].transcript;
                addText('box-in', text);
                
                const from = rec.lang.split('-')[0];
                const res = await fetch(`https://api.mymemory.translated.net/get?q=${encodeURIComponent(text)}&langpair=${from}|${targetLang}`);
                const data = await res.json();
                const trans = data.responseData.translatedText;
                
                addText('box-out', trans);
                if(document.getElementById('master-ia-audio').value === 'on') speak(trans);

                fetch("{{ route('transcripciones.store') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ sesion_id: '{{ $sesion->id }}', texto: trans, idioma: targetLang })
                });
            };
            rec.onend = () => { if(isLive) rec.start(); };
            rec.start();
        }

        function speak(t) {
            window.speechSynthesis.cancel();
            const m = new SpeechSynthesisUtterance(t);
            m.lang = targetLang;
            window.speechSynthesis.speak(m);
        }

        function addText(id, t) {
            const b = document.getElementById(id);
            const d = document.createElement('div');
            d.className = 'msg'; d.innerText = t;
            b.appendChild(d); b.scrollTop = b.scrollHeight;
        }

        function setLang(el, l) {
            document.querySelectorAll('.lang-card').forEach(c => c.classList.remove('active'));
            el.classList.add('active'); targetLang = l;
        }
    </script>
</body>
</html>