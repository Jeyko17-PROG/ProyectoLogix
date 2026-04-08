@use('SimpleSoftwareIO\QrCode\Facades\QrCode')
@extends('layouts.spikia')

@section('content')
@php
    $masterLanguages = config('spikia.master_languages', []);
    $sessionLanguages = is_array($sesion->idiomas ?? null) ? array_values($sesion->idiomas) : [];
    $targetLanguages = array_values(array_filter(array_map(fn ($lang) => $lang['id'] ?? null, $masterLanguages)));
    $masterLanguageLabels = [];
    foreach ($masterLanguages as $language) {
        if (! empty($language['id']) && ! empty($language['name'])) {
            $masterLanguageLabels[$language['id']] = $language['name'];
        }
    }
    $urlReunion = route('sesion.transmision', ['slug' => $sesion->slug]);
    $masterConfig = [
        'sesionId' => $sesion->id,
        'slug' => $sesion->slug,
        'socketUrl' => request()->getScheme() . '://' . request()->getHost() . ':3000',
        'relayUrl' => route('sesiones.mensajes.store', ['slug' => $sesion->slug]),
        'transcripcionUrl' => url('/transcripciones/guardar'),
        'csrfToken' => csrf_token(),
        'sessionLanguages' => $sessionLanguages,
        'targetLanguages' => $targetLanguages,
        'languageLabels' => $masterLanguageLabels,
        'defaultTargets' => config('spikia.default_targets', ['en', 'pt', 'it', 'fr']),
    ];
    $masterQrSvg = QrCode::format('svg')->errorCorrection('H')->size(300)->margin(2)->generate($urlReunion);
@endphp

@push('head-scripts')
<script>
    window.__SPIKIA_MASTER__ = @json($masterConfig);
</script>
@endpush

<div class="flex h-screen bg-black text-white font-sans overflow-hidden">
    <aside class="w-72 bg-zinc-950 border-r border-white/5 p-5 flex flex-col gap-5 relative z-20">
        <a href="{{ route('sesiones.index') }}" class="group flex items-center gap-2 text-zinc-500 hover:text-white transition-all mb-1">
            <div class="p-1.5 rounded-lg bg-zinc-900 group-hover:bg-zinc-800 border border-white/5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </div>
            <span class="text-[9px] font-black tracking-widest uppercase italic">Volver al listado</span>
        </a>

        <div class="flex flex-col items-center justify-center py-4 mb-2">
            <div class="relative group cursor-pointer" onclick="document.getElementById('qr-modal').classList.remove('hidden')">
                <div id="logo-aura" class="absolute inset-0 rounded-full bg-indigo-600/10 blur-xl scale-125 group-hover:bg-indigo-600/30 transition-all"></div>
                <div class="relative bg-zinc-900/40 backdrop-blur-3xl border border-white/10 rounded-[1.5rem] p-4 shadow-2xl transition-transform group-hover:scale-105">
                    <img src="{{ asset('storage/media/images/spikia-25.png') }}" class="h-14 w-auto object-contain" alt="Spikia">
                </div>
            </div>
            <span class="text-[7px] font-black text-zinc-600 uppercase tracking-[0.4em] mt-5 italic text-center underline decoration-indigo-600/40">Haz clic para ver el QR</span>
        </div>

        <div class="flex flex-col gap-2 p-4 bg-zinc-900/40 rounded-[1.5rem] border border-white/5">
            <span class="text-[8px] font-black text-zinc-600 uppercase tracking-[0.2em] text-center">Perfil de voz IA</span>
            <div class="flex gap-2">
                <button id="voice-male" class="voice-gender-btn flex-1 py-2 rounded-xl text-[9px] font-black border border-white/5 transition-all bg-indigo-600 text-white" data-gender="male">MALE</button>
                <button id="voice-female" class="voice-gender-btn flex-1 py-2 rounded-xl text-[9px] font-black border border-white/5 transition-all text-zinc-600 hover:text-white" data-gender="female">FEMALE</button>
            </div>
        </div>

        <div class="flex flex-col gap-2 p-4 bg-zinc-900/40 rounded-[1.5rem] border border-white/5">
            <span class="text-[8px] font-black text-zinc-600 uppercase tracking-[0.2em] text-center">Modo de guardado</span>
            <select id="save-mode" class="w-full bg-zinc-950 border border-white/10 rounded-xl px-3 py-2 text-[10px] font-black uppercase tracking-widest text-white">
                <option value="resumen" selected>Resumen</option>
                <option value="detalle">Detalle</option>
            </select>
        </div>

        <button id="master-live-btn" class="group relative w-full bg-zinc-900 border border-white/10 hover:border-indigo-600/50 rounded-[1.5rem] p-5 transition-all duration-500 overflow-hidden shadow-2xl">
            <div class="relative z-10 flex flex-col items-center gap-2">
                <div id="status-pulse" class="flex h-3 w-3">
                    <span id="status-dot" class="relative inline-flex rounded-full h-3 w-3 bg-zinc-700"></span>
                </div>
                <span id="status-text" class="text-[9px] font-black text-zinc-500 uppercase tracking-[0.3em]">En espera</span>
            </div>
            <div id="btn-bg-active" class="absolute inset-0 bg-gradient-to-br from-indigo-600/40 via-blue-500/10 to-transparent opacity-0 transition-opacity duration-700"></div>
        </button>

        <div class="bg-zinc-900/20 rounded-[1.5rem] border border-white/5 p-5 flex flex-col min-h-[140px]">
            <span class="text-[8px] font-black text-zinc-600 uppercase tracking-[0.2em] mb-4 text-center">Frecuencia de entrada</span>
            <div id="audio-visualizer" class="flex-1 flex items-end justify-center gap-1.5 px-1 pb-2">
                @for ($i = 0; $i < 15; $i++)
                    <div class="w-1 bg-zinc-800 rounded-full transition-all duration-150 bar" style="height: 15%"></div>
                @endfor
            </div>
        </div>
    </aside>

    <main class="flex-1 flex flex-col p-8 bg-black relative">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_-20%,#1e1b4b,transparent)] opacity-50"></div>

        <header class="relative z-10 mb-8 flex justify-between items-start">
            <div>
                <h2 class="text-4xl font-extralight tracking-tighter mb-2 text-white italic">Spikia <span class="font-black not-italic text-transparent bg-clip-text bg-gradient-to-r from-white via-zinc-400 to-zinc-600">Master Control</span></h2>
                <p class="text-zinc-500 font-bold text-[12px] tracking-widest uppercase italic">{{ $sesion->titulo }}</p>
                <div class="mt-4 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2">
                    <span class="text-[9px] font-black uppercase tracking-[0.3em] text-zinc-500">Idioma activo</span>
                    <span id="selected-language-label" class="text-[10px] font-black uppercase tracking-[0.22em] text-neonBlue">Español España</span>
                </div>
            </div>
            <div class="px-8 py-3 bg-zinc-950/80 backdrop-blur-xl rounded-[1.5rem] border border-white/10 shadow-2xl">
                <span id="session-timer" class="text-3xl font-light text-zinc-400 italic">00:00:00</span>
            </div>
        </header>

        <div class="relative z-10 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            @foreach($masterLanguages as $lang)
                <button type="button"
                    data-lang-id="{{ $lang['id'] }}"
                    data-lang-base="{{ $lang['base'] }}"
                    data-speech-lang="{{ $lang['speech'] }}"
                    data-lang-name="{{ $lang['name'] }}"
                    class="language-btn group p-4 rounded-[1.5rem] bg-zinc-900/30 border border-white/5 transition-all {{ $lang['id'] == 'es-ES' ? 'border-indigo-600 text-white' : '' }}">
                    <div class="font-black text-[9px] tracking-[0.15em] text-zinc-600 group-hover:text-white uppercase italic">{{ $lang['name'] }}</div>
                    <div class="mt-2 text-[8px] font-black uppercase tracking-[0.25em] text-zinc-700">{{ $lang['id'] }}</div>
                </button>
            @endforeach
        </div>

        <div class="relative z-10 flex-1 flex flex-col bg-zinc-950/20 backdrop-blur-sm border border-white/5 rounded-[3rem] p-8 overflow-hidden shadow-inner">
            <div id="transcription-box" class="flex-1 overflow-y-auto px-4 space-y-4 flex flex-col pt-10">
                <p id="placeholder-text" class="text-lg font-light text-zinc-800 italic uppercase text-center">Esperando señal de voz...</p>
            </div>
        </div>
    </main>
</div>

<div id="qr-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-black/95 backdrop-blur-md">
    <div class="absolute inset-0" onclick="document.getElementById('qr-modal').classList.add('hidden')"></div>
    <div class="relative bg-zinc-900 border border-white/10 p-10 rounded-[3rem] text-center max-w-lg shadow-[0_0_150px_rgba(112,0,255,0.2)] backdrop-blur-xl">
        <h3 class="text-3xl font-black italic uppercase tracking-tighter mb-4 text-white">Acceso de invitados</h3>
        <div id="master-qr-wrap" class="bg-white p-4 rounded-[2rem] inline-block shadow-2xl border-[8px] border-indigo-600/10 ring-1 ring-black/5 overflow-hidden">
            {!! $masterQrSvg !!}
        </div>
        <div class="mt-6">
            <code class="text-indigo-400 font-bold text-[10px] bg-black/50 px-4 py-2 rounded-full border border-white/5 break-all">{{ $urlReunion }}</code>
            <div class="mt-6 flex flex-col gap-3">
                <button onclick="downloadMasterQrPng()" class="px-8 py-3 bg-zinc-800 text-white hover:bg-neonBlue hover:text-black rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all">
                    Descargar PNG
                </button>
                <button onclick="document.getElementById('qr-modal').classList.add('hidden')" class="px-8 py-3 bg-white text-black hover:bg-indigo-600 hover:text-white rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all">
                    Cerrar panel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function downloadMasterQrPng() {
        const wrapper = document.getElementById('master-qr-wrap');
        const svg = wrapper ? wrapper.querySelector('svg') : null;

        if (!svg) {
            alert('No se encontró el QR.');
            return;
        }

        const svgData = new XMLSerializer().serializeToString(svg);
        const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
        const url = URL.createObjectURL(svgBlob);
        const img = new Image();

        img.onload = () => {
            const canvas = document.createElement('canvas');
            const size = Math.max(img.width, img.height) || 1024;
            canvas.width = size;
            canvas.height = size;

            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, size, size);
            ctx.drawImage(img, 0, 0, size, size);

            canvas.toBlob((blob) => {
                if (!blob) {
                    alert('No se pudo generar el PNG.');
                    URL.revokeObjectURL(url);
                    return;
                }

                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'qr-master-{{ $sesion->slug }}.png';
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(link.href);
                URL.revokeObjectURL(url);
            }, 'image/png');
        };

        img.onerror = () => {
            alert('No se pudo convertir el QR a PNG.');
            URL.revokeObjectURL(url);
        };

        img.src = url;
    }

</script>
@endsection
