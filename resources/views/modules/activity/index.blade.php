@extends('layouts.spikia')

@section('content')
<div class="min-h-screen bg-[#050505] text-white font-sans selection:bg-spikiaPurple/30 selection:text-white">
    <div class="fixed inset-0 bg-[radial-gradient(circle_at_top_left,rgba(112,0,255,0.18),transparent_35%),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.10),transparent_30%)] pointer-events-none"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 py-8 lg:px-10 lg:py-10">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-8 mb-10">
            <div class="space-y-5">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.35em] text-zinc-500 hover:text-white transition">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-white/10 bg-white/5"><-</span>
                    Volver al panel
                </a>

                <div class="space-y-3 max-w-3xl">
                    <p class="text-[10px] font-black uppercase tracking-[0.45em] text-zinc-600">Registro central</p>
                    <h1 class="text-4xl lg:text-5xl font-black italic tracking-tight uppercase leading-[0.95]">
                        Registro de <span class="text-[#7000ff]">Log</span>
                    </h1>
                    <p class="text-zinc-400 max-w-2xl leading-7 text-sm lg:text-base">
                        Documento automatico que registra cronologicamente eventos, acciones o errores de cada sesion. Aqui puedes revisar que ocurrio, cuando sucedio y cuantas transcripciones quedaron asociadas.
                    </p>
                </div>
            </div>

            <div class="flex flex-col items-start lg:items-end gap-4">
                @if(Storage::disk('public')->exists('media/images/spikia-25.png'))
                    <img src="{{ asset('storage/media/images/spikia-25.png') }}" class="h-20 w-auto opacity-90" alt="Spikia">
                @endif

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 min-w-[110px]">
                        <p class="text-[9px] font-black uppercase tracking-[0.35em] text-zinc-500">Sesiones</p>
                        <p class="mt-2 text-2xl font-black">{{ count($sesionesConEstadisticas ?? []) }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 min-w-[110px]">
                        <p class="text-[9px] font-black uppercase tracking-[0.35em] text-zinc-500">Transcripciones</p>
                        <p class="mt-2 text-2xl font-black">{{ collect($sesionesConEstadisticas ?? [])->sum('transcripciones_count') }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 min-w-[110px]">
                        <p class="text-[9px] font-black uppercase tracking-[0.35em] text-zinc-500">Horas</p>
                        <p class="mt-2 text-2xl font-black">
                            {{ number_format(collect($sesionesConEstadisticas ?? [])->sum(fn ($item) => (float) ($item['duracion_horas'] ?? 0)), 2) }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 min-w-[110px]">
                        <p class="text-[9px] font-black uppercase tracking-[0.35em] text-zinc-500">Filtro</p>
                        <p class="mt-2 text-2xl font-black uppercase">{{ !empty($q) ? 'ON' : 'ALL' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-[2.5rem] border border-white/8 bg-zinc-950/60 backdrop-blur-md p-5 lg:p-6 mb-8 shadow-2xl">
            <form method="GET" action="{{ route('actividad.index') }}" class="grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-4 items-end">
                <div>
                    <label for="q" class="block text-[9px] font-black uppercase tracking-[0.35em] text-zinc-500 mb-3">Buscar sesion, fecha, usuario o texto</label>
                    <input
                        id="q"
                        name="q"
                        value="{{ $q ?? '' }}"
                        type="text"
                        placeholder="Ej. reunion, 2026-04, cliente..."
                        class="w-full rounded-2xl border border-white/10 bg-black/40 px-5 py-4 text-sm text-white placeholder:text-zinc-600 outline-none transition focus:border-[#7000ff]/50 focus:ring-2 focus:ring-[#7000ff]/20"
                    >
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="px-5 py-4 rounded-2xl bg-[#7000ff] text-white text-[10px] font-black uppercase tracking-[0.35em] hover:scale-[1.01] transition">
                        Buscar
                    </button>
                    @if(($q ?? '') !== '')
                        <a href="{{ route('actividad.index') }}" class="px-5 py-4 rounded-2xl border border-white/10 bg-white/5 text-zinc-300 text-[10px] font-black uppercase tracking-[0.35em] hover:border-white/25 hover:text-white transition">
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('actividad.export', array_filter(['q' => $q ?? null])) }}" class="group flex items-center gap-3 px-6 py-3 bg-emerald-500/5 border border-emerald-500/10 rounded-2xl hover:bg-emerald-500/10 hover:border-emerald-500/40 transition-all duration-300 shadow-xl">
                    <div class="p-2 rounded-lg bg-emerald-500/10 border border-emerald-500/20 group-hover:bg-emerald-500/30 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="flex flex-col text-left">
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-500 italic">Generar Log</span>
                        <span class="text-[8px] text-zinc-500 font-bold uppercase tracking-tighter">Exportar a Excel (.xlsx)</span>
                    </div>
                </a>

                @if(($q ?? '') !== '')
                    <span class="px-4 py-2 rounded-full bg-white/5 border border-white/10 text-[9px] font-black uppercase tracking-[0.3em] text-zinc-300">
                        Resultado para "{{ $q }}"
                    </span>
                @endif
            </div>

            <div class="text-right">
                <span class="text-3xl font-light text-white italic leading-none block">{{ count($sesionesConEstadisticas ?? []) }}</span>
                <span class="text-[8px] font-black text-zinc-700 uppercase tracking-[0.3em]">Registros indexados</span>
            </div>
        </div>

        <div class="space-y-5">
            @forelse($sesionesConEstadisticas ?? [] as $sesion)
                <article class="group rounded-[2.5rem] border border-white/5 bg-zinc-950/55 shadow-2xl overflow-hidden backdrop-blur-md">
                    <div class="flex flex-col xl:flex-row xl:items-stretch">
                        <div class="xl:w-[34%] p-6 lg:p-8 border-b xl:border-b-0 xl:border-r border-white/5 bg-white/[0.02]">
                            <div class="flex items-center justify-between gap-3 mb-6">
                                <span class="px-3 py-1 rounded-full bg-[#7000ff]/10 text-[#b98cff] border border-[#7000ff]/20 text-[9px] font-black uppercase tracking-widest">
                                    Sesion vinculada
                                </span>
                                <span class="px-3 py-1 rounded-full bg-white/5 text-zinc-400 border border-white/10 text-[9px] font-black uppercase tracking-widest">
                                    {{ $sesion['transcripciones_count'] ?? 0 }} transcripciones
                                </span>
                            </div>

                            <h2 class="text-2xl lg:text-3xl font-black italic uppercase tracking-tight text-white leading-tight group-hover:text-[#b98cff] transition-colors">
                                {{ $sesion['titulo'] }}
                            </h2>

                            <div class="mt-4 space-y-3 text-sm text-zinc-400">
                                <div class="flex items-center gap-2">
                                    <span class="text-zinc-600 text-[9px] font-black uppercase tracking-[0.3em] min-w-[72px]">Usuario</span>
                                    <span class="text-zinc-300 font-medium">{{ $sesion['presentador'] ?? 'Usuario' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-zinc-600 text-[9px] font-black uppercase tracking-[0.3em] min-w-[72px]">Fecha</span>
                                    <span>{{ $sesion['fecha'] ?? 'Sin fecha' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-zinc-600 text-[9px] font-black uppercase tracking-[0.3em] min-w-[72px]">Horario</span>
                                    <span>{{ $sesion['hora_inicio'] ?? '--:--' }} a {{ $sesion['hora_fin'] ?? '--:--' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-zinc-600 text-[9px] font-black uppercase tracking-[0.3em] min-w-[72px]">Duracion</span>
                                    <span>{{ number_format((float) ($sesion['duracion_horas'] ?? 0), 2) }} h</span>
                                </div>
                            </div>
                        </div>

                        <div class="xl:flex-1 p-6 lg:p-8 flex flex-col gap-6">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-[0.35em] text-zinc-500">Ultimo texto registrado</p>
                                    <p class="mt-2 text-zinc-200 text-sm leading-7 max-w-4xl">
                                        {{ !empty($sesion['ultimo_texto']) ? \Illuminate\Support\Str::limit($sesion['ultimo_texto'], 260) : 'Todavia no hay texto capturado para esta sesion.' }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div class="rounded-2xl border border-white/5 bg-white/[0.03] px-4 py-4">
                                    <p class="text-[9px] font-black uppercase tracking-[0.3em] text-zinc-600">Sesion</p>
                                    <p class="mt-2 text-lg font-black text-white">{{ $sesion['titulo'] }}</p>
                                </div>
                                <div class="rounded-2xl border border-white/5 bg-white/[0.03] px-4 py-4">
                                    <p class="text-[9px] font-black uppercase tracking-[0.3em] text-zinc-600">Fecha</p>
                                    <p class="mt-2 text-lg font-black text-white">{{ $sesion['fecha'] ?? 'N/A' }}</p>
                                </div>
                                <div class="rounded-2xl border border-white/5 bg-white/[0.03] px-4 py-4">
                                    <p class="text-[9px] font-black uppercase tracking-[0.3em] text-zinc-600">Duracion</p>
                                    <p class="mt-2 text-lg font-black text-white">{{ number_format((float) ($sesion['duracion_horas'] ?? 0), 2) }} h</p>
                                </div>
                                <div class="rounded-2xl border border-white/5 bg-white/[0.03] px-4 py-4">
                                    <p class="text-[9px] font-black uppercase tracking-[0.3em] text-zinc-600">Transcripciones</p>
                                    <p class="mt-2 text-lg font-black text-white">{{ $sesion['transcripciones_count'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="bg-zinc-900/20 border border-dashed border-white/10 rounded-[2.5rem] p-20 text-center">
                    <p class="text-[10px] font-black uppercase tracking-[0.4em] text-zinc-500">No hay actividad registrada</p>
                    @if(($q ?? '') !== '')
                        <p class="mt-4 text-sm text-zinc-500">No encontramos coincidencias para tu busqueda.</p>
                    @endif
                </div>
            @endforelse
        </div>

        <footer class="mt-20 flex flex-col items-center gap-6 opacity-30">
            <div class="h-[1px] w-40 bg-gradient-to-r from-transparent via-zinc-500 to-transparent"></div>
            <p class="text-[9px] font-black text-zinc-500 uppercase tracking-[0.8em]">Spikia Control Interface v2.0</p>
        </footer>
    </div>
</div>

<style>
    body { -webkit-font-smoothing: antialiased; letter-spacing: -0.01em; }
    ::-webkit-scrollbar { width: 4px; height: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #1a1a1a; border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #333; }
</style>
@endsection