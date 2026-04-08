@extends('layouts.spikia')

@section('content')
@php
    $languageLabels = [
        'es' => 'Español',
        'en' => 'Inglés',
        'pt' => 'Portugués',
        'fr' => 'Francés',
        'de' => 'Alemán',
        'it' => 'Italiano',
    ];

    $languageStyles = [
        'es' => 'text-cyan-300 bg-cyan-500/10 border-cyan-500/20',
        'en' => 'text-emerald-300 bg-emerald-500/10 border-emerald-500/20',
        'pt' => 'text-amber-300 bg-amber-500/10 border-amber-500/20',
        'fr' => 'text-fuchsia-300 bg-fuchsia-500/10 border-fuchsia-500/20',
        'de' => 'text-violet-300 bg-violet-500/10 border-violet-500/20',
        'it' => 'text-rose-300 bg-rose-500/10 border-rose-500/20',
    ];
@endphp
<div class="p-8 bg-[#050505] min-h-screen text-white font-sans">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-12">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}"
                   class="group flex items-center justify-center w-12 h-12 rounded-2xl bg-white/5 border border-white/10 hover:bg-[#00d2ff] hover:border-[#00d2ff] transition-all shadow-lg">
                    <svg class="w-5 h-5 text-zinc-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h2 class="text-4xl font-black italic tracking-tighter uppercase leading-none">
                        Mis <span class="text-[#00d2ff]">Glosarios</span>
                    </h2>
                    <p class="text-zinc-500 text-[10px] font-black uppercase tracking-[0.3em] mt-2 italic">Biblioteca de términos personalizados</p>
                </div>
            </div>
            <img src="{{ asset('storage/media/images/spikia-25.png') }}" class="h-20 w-auto opacity-80 hover:opacity-100 transition-opacity" alt="Spikia">
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <div class="lg:col-span-5 space-y-4">
                <div class="space-y-3">
                    @forelse($glosarios as $g)
                        @php
                            $idioma = $g->idioma ?? 'es';
                            $idiomaLabel = $languageLabels[$idioma] ?? strtoupper($idioma);
                            $style = $languageStyles[$idioma] ?? 'text-zinc-300 bg-white/5 border-white/10';
                        @endphp
                        <div class="bg-zinc-900/30 border border-white/5 p-6 rounded-[2rem] hover:bg-zinc-900/60 transition-all group flex justify-between items-center backdrop-blur-sm">
                            <div class="space-y-3">
                                <p class="font-bold text-base tracking-tight group-hover:text-[#00d2ff] transition-colors uppercase italic">{{ $g->titulo }}</p>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[9px] font-black uppercase tracking-[0.25em] {{ $style }}">
                                        <span class="h-2 w-2 rounded-full bg-current"></span>
                                        {{ $idiomaLabel }}
                                    </span>
                                    <span class="px-2 py-0.5 bg-white/5 rounded text-[8px] font-black uppercase text-[#00d2ff]">{{ strtoupper($idioma) }}</span>
                                    <p class="text-zinc-600 text-[9px] font-black uppercase tracking-[0.2em]">Sincronizado</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    onclick='editGlosario(@json($g->id), @json($g->titulo), @json($g->terminos), @json($g->idioma ?? "es"))'
                                    class="w-10 h-10 flex items-center justify-center bg-zinc-800/50 rounded-xl text-zinc-500 hover:text-[#00d2ff] hover:bg-[#00d2ff]/10 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>

                                <form action="{{ route('glosarios.destroy', $g->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este glosario?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-10 h-10 flex items-center justify-center bg-zinc-800/50 rounded-xl text-zinc-500 hover:text-red-500 hover:bg-red-500/10 transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-16 text-center border-2 border-dashed border-white/5 rounded-[2.5rem] text-zinc-700">
                            <p class="text-[10px] font-black uppercase tracking-widest italic">No hay glosarios creados</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="lg:col-span-7">
                <div class="bg-zinc-900/20 border border-white/10 rounded-[3rem] p-10 backdrop-blur-md shadow-2xl relative overflow-hidden">
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-transparent via-[#00d2ff] to-transparent"></div>
                    <div class="flex justify-between items-center mb-8 border-b border-white/5 pb-8">
                        <h3 id="formTitle" class="text-xl font-[900] uppercase tracking-[0.2em] italic text-[#00d2ff]">Configurar Glosario</h3>
                        <button type="button" onclick="resetForm()" class="bg-[#7000ff] hover:bg-[#00d2ff] text-white px-6 py-2.5 rounded-full text-[10px] font-black transition-all shadow-lg uppercase tracking-widest">
                            + NUEVO REGISTRO
                        </button>
                    </div>

                    <form id="glosarioForm" action="{{ route('glosarios.store') }}" method="POST" class="space-y-8">
                        @csrf
                        <input type="hidden" name="_method" id="formMethod" value="POST">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-zinc-500 uppercase tracking-[0.4em] mb-4 ml-4 italic">Título del glosario</label>
                                <input type="text" name="titulo" id="titulo" required placeholder="Ej: Terminología Médica"
                                    class="w-full bg-black/60 border border-white/5 rounded-2xl px-6 py-5 focus:border-[#00d2ff] outline-none text-white font-bold text-sm transition-all shadow-inner">
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-zinc-500 uppercase tracking-[0.4em] mb-4 ml-4 italic">Idioma de origen</label>
                                <select name="idioma" id="idioma" class="w-full bg-black/60 border border-white/5 rounded-2xl px-6 py-5 focus:border-[#00d2ff] outline-none text-white font-bold text-sm appearance-none cursor-pointer transition-all">
                                    <option value="es" @selected(old('idioma', 'es') === 'es')>Español (Latam/ES)</option>
                                    <option value="en" @selected(old('idioma') === 'en')>Inglés (US/UK)</option>
                                    <option value="pt" @selected(old('idioma') === 'pt')>Portugués</option>
                                    <option value="fr" @selected(old('idioma') === 'fr')>Francés</option>
                                    <option value="de" @selected(old('idioma') === 'de')>Alemán</option>
                                    <option value="it" @selected(old('idioma') === 'it')>Italiano</option>
                                </select>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($languageLabels as $code => $label)
                                        <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[9px] font-black uppercase tracking-[0.25em] text-zinc-400">
                                            {{ $label }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-zinc-500 uppercase tracking-[0.4em] mb-4 ml-4 italic">Términos de refuerzo (IA)</label>
                            <textarea name="terminos" id="terminos" rows="6" placeholder="Escribe las palabras separadas por comas (ej: Spikia, LogixFix, Blockchain)..."
                                    class="w-full bg-black/60 border border-white/5 rounded-[2rem] px-6 py-6 focus:border-[#7000ff] outline-none text-zinc-300 text-sm resize-none transition-all shadow-inner"></textarea>
                        </div>

                        <div class="flex justify-end pt-6">
                            <button type="submit" id="submitBtn" class="bg-white text-black px-14 py-5 rounded-2xl font-[900] hover:bg-[#00d2ff] hover:text-white transition-all uppercase text-[11px] tracking-[0.3em] shadow-xl transform active:scale-95">
                                Guardar Glosario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function editGlosario(id, titulo, terminos, idioma) {
        document.getElementById('formTitle').innerText = 'Editar Glosario';
        document.getElementById('submitBtn').innerText = 'Actualizar Glosario';

        const form = document.getElementById('glosarioForm');
        form.action = `/glosarios/${id}`;

        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('titulo').value = titulo ?? '';
        document.getElementById('terminos').value = terminos ?? '';
        document.getElementById('idioma').value = idioma || 'es';

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetForm() {
        document.getElementById('formTitle').innerText = 'Configurar Glosario';
        document.getElementById('submitBtn').innerText = 'Guardar Glosario';

        const form = document.getElementById('glosarioForm');
        form.action = "{{ route('glosarios.store') }}";
        document.getElementById('formMethod').value = 'POST';

        form.reset();
        document.getElementById('idioma').value = 'es';
    }
</script>
@endsection


