@use('SimpleSoftwareIO\QrCode\Facades\QrCode')
@extends('layouts.spikia')

@push('styles')
@vite('resources/css/sessions-index.css')
@endpush

@section('content')
<div class="min-h-screen bg-[#050505] text-white px-6 py-10">
    <div class="max-w-7xl mx-auto space-y-8">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
            <div class="space-y-4">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.35em] text-zinc-500 hover:text-white transition">
                    <span>&larr;</span> Volver al panel
                </a>
                <div class="flex items-center gap-5">
                    <img src="{{ asset('storage/media/images/spikia-25.png') }}" class="h-16 w-auto drop-shadow-[0_0_18px_rgba(124,58,237,0.35)]" alt="Spikia">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-zinc-500">Sesiones privadas</p>
                        <h1 class="text-4xl font-black italic tracking-tighter uppercase">Panel de sesiones</h1>
                    </div>
                </div>
            </div>

            <button onclick="document.getElementById('createModal').style.display='block'" class="self-start rounded-2xl bg-white px-6 py-4 text-[10px] font-black uppercase tracking-[0.35em] text-black transition hover:bg-neonBlue hover:text-white">
                + Nueva sesión
            </button>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-[1.8rem] border border-white/10 bg-white/5 p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-zinc-500">Sesiones</p>
                <p class="mt-3 text-3xl font-black">{{ $resumenContenido['sesiones'] ?? 0 }}</p>
            </div>
            <div class="rounded-[1.8rem] border border-white/10 bg-white/5 p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-zinc-500">Glosarios</p>
                <p class="mt-3 text-3xl font-black">{{ $resumenContenido['glosarios'] ?? 0 }}</p>
            </div>
            <div class="rounded-[1.8rem] border border-white/10 bg-white/5 p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-zinc-500">Transcripciones</p>
                <p class="mt-3 text-3xl font-black">{{ $resumenContenido['transcripciones'] ?? 0 }}</p>
            </div>
            <div class="rounded-[1.8rem] border border-white/10 bg-white/5 p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-zinc-500">Videos</p>
                <p class="mt-3 text-3xl font-black">{{ $resumenContenido['videos'] ?? 0 }}</p>
            </div>
        </div>

        <div class="rounded-[2rem] border border-white/10 bg-zinc-950/70 overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-black/70">
                        <tr>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.35em] text-zinc-500">QR</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.35em] text-zinc-500">Sesión</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.35em] text-zinc-500">Usuario</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.35em] text-zinc-500">Contenido</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.35em] text-zinc-500">Horario</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.35em] text-zinc-500 text-right">Gestión</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sesiones as $s)
                            @php
                                $urlTransmision = route('sesion.transmision', ['slug' => $s->slug]);
                                $ownerName = $s->user?->name ?? auth()->user()->name;
                                $ownerEmail = $s->user?->email ?? auth()->user()->email;
                                $qrSvg = QrCode::format('svg')->errorCorrection('H')->size(176)->margin(2)->generate($urlTransmision);
                            @endphp
                            <tr class="border-t border-white/5 hover:bg-white/[0.02] transition">
                                <td class="px-6 py-6 align-top">
                                    <div class="inline-flex flex-col items-center gap-4">
                                        <div id="qr-wrap-{{ $s->slug }}" class="w-[190px] aspect-square rounded-[1.5rem] bg-white p-4 shadow-[0_12px_28px_rgba(0,0,0,0.20)] ring-1 ring-black/5 overflow-hidden flex items-center justify-center [&_svg]:block [&_svg]:w-full [&_svg]:h-full [&_svg]:max-w-full [&_svg]:max-h-full">
                                            {!! $qrSvg !!}
                                        </div>
                                        <div class="space-y-2 text-center">
                                            <a href="{{ $urlTransmision }}" target="_blank" class="block text-[9px] font-black uppercase tracking-[0.25em] text-zinc-400 hover:text-white transition">
                                                Abrir transmisión
                                            </a>
                                            <a href="{{ route('sesion.master', $s->slug) }}" target="_blank" class="block text-[9px] font-black uppercase tracking-[0.25em] text-zinc-400 hover:text-white transition">
                                                Abrir master
                                            </a>
                                            <button type="button" onclick="downloadQrPng('{{ $s->slug }}')" class="block text-[9px] font-black uppercase tracking-[0.25em] text-neonBlue hover:text-white transition">
                                                Descargar PNG
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" onclick="copyToClipboard('{{ $urlTransmision }}')" class="mx-auto mt-3 block text-[9px] font-black uppercase tracking-[0.25em] text-zinc-500 hover:text-neonBlue transition">
                                        Copiar enlace
                                    </button>
                                </td>
                                <td class="px-6 py-6 align-top">
                                    <h2 class="text-2xl font-black uppercase italic tracking-tight">{{ $s->titulo }}</h2>
                                    <p class="mt-2 text-[10px] font-black uppercase tracking-[0.3em] text-zinc-600">
                                        Glosario: {{ $s->glosario?->titulo ?? 'Estándar' }}
                                    </p>
                                    <div class="mt-4 flex flex-wrap gap-3">
                                        <a href="{{ route('sesion.master', $s->slug) }}" class="inline-flex min-w-[104px] items-center justify-center rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-[9px] font-black uppercase tracking-[0.3em] hover:border-spikiaPurple/50 hover:text-white transition">
                                            Master
                                        </a>
                                        <a href="{{ $urlTransmision }}" class="inline-flex min-w-[104px] items-center justify-center rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-[9px] font-black uppercase tracking-[0.3em] hover:border-neonBlue/50 hover:text-white transition">
                                            Transmisión
                                        </a>
                                    </div>
                                </td>
                                <td class="px-6 py-6 align-top">
                                    <p class="font-bold text-white">{{ $ownerName }}</p>
                                    <p class="mt-1 text-[10px] text-zinc-500">{{ $ownerEmail }}</p>
                                </td>
                                <td class="px-6 py-6 align-top">
                                    <div class="rounded-2xl border border-white/5 bg-black/30 px-4 py-3">
                                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-zinc-500">Transcripciones</p>
                                        <p class="mt-2 text-2xl font-black">{{ $s->transcripciones_count ?? 0 }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-6 align-top">
                                    <p class="text-[11px] font-black uppercase tracking-[0.25em] text-zinc-500">{{ $s->fecha_inicio }}</p>
                                    <div class="mt-2 flex items-center gap-2 text-lg font-light text-white">
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-white/10 bg-white/5 text-[10px]">⏱</span>
                                        <span>Inicio</span>
                                    </div>
                                    <p class="mt-1 text-lg font-light text-neonBlue">Fin: {{ $s->hora_fin ?? '--:--' }}</p>
                                </td>
                                <td class="px-6 py-6 align-top text-right">
                                    <div class="flex flex-col items-end gap-3">
                                        <a href="{{ route('sesiones.edit', $s->id) }}" class="inline-flex w-[136px] items-center justify-center rounded-xl border border-white/10 px-4 py-2 text-[9px] font-black uppercase tracking-[0.25em] text-neonBlue hover:text-white hover:border-neonBlue/40 transition">
                                            Configuración
                                        </a>
                                        <form action="{{ route('sesiones.destroy', $s->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta sesión?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex w-[136px] items-center justify-center rounded-xl border border-white/10 px-4 py-2 text-[9px] font-black uppercase tracking-[0.3em] text-red-400 hover:text-red-300 transition">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-20 text-center text-[10px] font-black uppercase tracking-[0.4em] text-zinc-600">
                                    No hay sesiones disponibles
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div id="createModal" class="fixed inset-0 z-50 hidden bg-black/90 backdrop-blur-md">
            <div class="absolute inset-0" onclick="document.getElementById('createModal').style.display='none'"></div>
            <div class="relative mx-auto mt-10 w-[92%] max-w-2xl rounded-[2rem] border border-white/10 bg-zinc-950 p-8 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.35em] text-zinc-500">Nueva sesión</p>
                        <h2 class="text-3xl font-black italic tracking-tighter">Crear sesión</h2>
                    </div>
                    <button type="button" onclick="document.getElementById('createModal').style.display='none'" class="text-zinc-500 hover:text-white transition">✕</button>
                </div>

                <form action="{{ route('sesiones.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-[0.35em] text-zinc-500 mb-2">Título</label>
                        <input type="text" name="titulo" placeholder="Ej: Congreso de Medicina" required class="w-full rounded-2xl border border-white/10 bg-black/60 px-5 py-4 text-white outline-none focus:border-neonBlue">
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-[0.35em] text-zinc-500 mb-2">Fecha</label>
                            <input type="date" name="fecha_inicio" required class="w-full rounded-2xl border border-white/10 bg-black/60 px-5 py-4 text-white outline-none focus:border-neonBlue">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-[0.35em] text-zinc-500 mb-2">Hora de inicio</label>
                            <input type="time" name="hora_inicio" required class="w-full rounded-2xl border border-white/10 bg-black/60 px-5 py-4 text-white outline-none focus:border-neonBlue">
                        </div>
                    </div>
                    <div>
                        <div class="mb-2 flex items-center gap-2">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-white/10 bg-white/5 text-[10px]">⏱</span>
                            <label class="block text-[10px] font-black uppercase tracking-[0.35em] text-zinc-500">Hora de término</label>
                        </div>
                        <input type="time" name="hora_fin" class="w-full rounded-2xl border border-white/10 bg-black/60 px-5 py-4 text-white outline-none focus:border-neonBlue">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-[0.35em] text-zinc-500 mb-2">Glosario</label>
                        <select name="glosario_id" class="w-full rounded-2xl border border-white/10 bg-black/60 px-5 py-4 text-white outline-none focus:border-neonBlue">
                            <option value="">Ninguno (Voz estándar)</option>
                            @foreach($glosarios as $g)
                                <option value="{{ $g->id }}">{{ $g->titulo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-[0.35em] text-zinc-500 mb-3">Idiomas de traducción</label>
                        <div class="grid gap-3 md:grid-cols-2 rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                            <label class="flex items-center gap-3 text-sm text-zinc-300"><input type="checkbox" name="idiomas[]" value="es-ES"> Español España</label>
                            <label class="flex items-center gap-3 text-sm text-zinc-300"><input type="checkbox" name="idiomas[]" value="es-419"> Español LatAm</label>
                            <label class="flex items-center gap-3 text-sm text-zinc-300"><input type="checkbox" name="idiomas[]" value="en"> Inglés</label>
                            <label class="flex items-center gap-3 text-sm text-zinc-300"><input type="checkbox" name="idiomas[]" value="fr"> Francés</label>
                            <label class="flex items-center gap-3 text-sm text-zinc-300"><input type="checkbox" name="idiomas[]" value="pt"> Portugués</label>
                            <label class="flex items-center gap-3 text-sm text-zinc-300"><input type="checkbox" name="idiomas[]" value="de"> Alemán</label>
                            <label class="flex items-center gap-3 text-sm text-zinc-300"><input type="checkbox" name="idiomas[]" value="it"> Italiano</label>
                        </div>
                    </div>
                    <button type="submit" class="w-full rounded-2xl bg-white px-6 py-4 text-[10px] font-black uppercase tracking-[0.35em] text-black transition hover:bg-neonBlue hover:text-white">
                        Crear sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Enlace copiado al portapapeles');
        });
    }

    async function downloadQrPng(slug) {
        const wrapper = document.getElementById(`qr-wrap-${slug}`);
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
                link.download = `qr-${slug}.png`;
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
