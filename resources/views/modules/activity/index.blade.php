@extends('layouts.spikia')

@section('title', 'Actividad | Spikia')

@push('styles')
@vite('resources/css/activity.css')
@endpush

@section('content')
<div class="min-h-screen bg-[#050505] text-white">
    <div class="spikia-page">
        <div class="mb-10 flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
            <div class="space-y-5">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.35em] text-zinc-500 transition hover:text-white">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-white/10 bg-white/5">&larr;</span>
                    Volver al panel
                </a>

                <div class="max-w-3xl space-y-3">
                    <p class="text-[10px] font-black uppercase tracking-[0.45em] text-zinc-600">Registro central</p>
                    <h1 class="text-4xl font-black italic uppercase leading-[0.95] tracking-tight">
                        Registro de <span class="text-[#7000ff]">actividad</span>
                    </h1>
                    <p class="max-w-2xl text-sm leading-7 text-zinc-400 lg:text-base">
                        Documento automatico que organiza cronologicamente eventos, acciones y errores de cada sesion. Aqui puedes revisar lo ocurrido y descargarlo en formato Excel.
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-4 lg:items-end">
                @if(Storage::disk('public')->exists('media/images/spikia-25.png'))
                    <img src="{{ asset('storage/media/images/spikia-25.png') }}" class="h-20 w-auto opacity-90" alt="Spikia">
                @endif

                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <div class="min-w-[110px] rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <p class="text-[9px] font-black uppercase tracking-[0.35em] text-zinc-500">Sesiones</p>
                        <p class="mt-2 text-2xl font-black">{{ $activityTotals['sesiones'] ?? (method_exists($sesionesConEstadisticas, 'total') ? $sesionesConEstadisticas->total() : count($sesionesConEstadisticas ?? [])) }}</p>
                    </div>
                    <div class="min-w-[110px] rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <p class="text-[9px] font-black uppercase tracking-[0.35em] text-zinc-500">Transcripciones</p>
                        <p class="mt-2 text-2xl font-black">{{ $activityTotals['transcripciones'] ?? collect($sesionesConEstadisticas ?? [])->sum('transcripciones_count') }}</p>
                    </div>
                    <div class="min-w-[110px] rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <p class="text-[9px] font-black uppercase tracking-[0.35em] text-zinc-500">Horas</p>
                        <p class="mt-2 text-2xl font-black">
                            {{ number_format(collect($sesionesConEstadisticas ?? [])->sum(fn ($item) => (float) ($item['duracion_horas'] ?? 0)), 2) }}
                        </p>
                    </div>
                    <div class="min-w-[110px] rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <p class="text-[9px] font-black uppercase tracking-[0.35em] text-zinc-500">Filtro</p>
                        <p class="mt-2 text-2xl font-black uppercase">{{ !empty($q) ? 'ON' : 'ALL' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="activity-scroll mb-8 rounded-[2.5rem] border border-white/8 bg-zinc-950/60 p-5 shadow-2xl backdrop-blur-md lg:p-6">
            <form method="GET" action="{{ route('actividad.index') }}" class="grid grid-cols-1 gap-4 items-end lg:grid-cols-[1fr_auto]">
                <div>
                    <label for="q" class="mb-3 block text-[9px] font-black uppercase tracking-[0.35em] text-zinc-500">Buscar sesion, fecha, usuario o texto</label>
                    <input
                        id="q"
                        name="q"
                        value="{{ $q ?? '' }}"
                        type="text"
                        placeholder="Ej. reunion, 2026-04, cliente..."
                        class="w-full rounded-2xl border border-white/10 bg-black/40 px-5 py-4 text-sm text-white outline-none transition placeholder:text-zinc-600 focus:border-[#7000ff]/50 focus:ring-2 focus:ring-[#7000ff]/20"
                    >
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="rounded-2xl bg-[#7000ff] px-5 py-4 text-[10px] font-black uppercase tracking-[0.35em] text-white transition hover:scale-[1.01]">
                        Buscar
                    </button>
                    @if(($q ?? '') !== '')
                        <a href="{{ route('actividad.index') }}" class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4 text-[10px] font-black uppercase tracking-[0.35em] text-zinc-300 transition hover:border-white/25 hover:text-white">
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('actividad.export', array_filter(['q' => $q ?? null])) }}" class="group flex items-center gap-3 rounded-2xl border border-emerald-500/10 bg-emerald-500/5 px-6 py-3 shadow-xl transition-all duration-300 hover:border-emerald-500/40 hover:bg-emerald-500/10">
                    <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-2 transition-colors group-hover:bg-emerald-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="flex flex-col text-left">
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-500 italic">Generar archivo Excel</span>
                        <span class="text-[8px] font-bold uppercase tracking-tighter text-zinc-500">Exportar a Excel (.xlsx)</span>
                    </div>
                </a>

                @if(($q ?? '') !== '')
                    <span class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-[9px] font-black uppercase tracking-[0.3em] text-zinc-300">
                        Resultado para "{{ $q }}"
                    </span>
                @endif
            </div>

            <div class="text-right">
                <span class="block text-3xl font-light leading-none text-white italic">{{ method_exists($sesionesConEstadisticas, 'total') ? $sesionesConEstadisticas->total() : count($sesionesConEstadisticas ?? []) }}</span>
                <span class="text-[8px] font-black uppercase tracking-[0.3em] text-zinc-700">Registros indexados</span>
            </div>
        </div>

        <div class="space-y-5">
            @forelse($sesionesConEstadisticas ?? [] as $sesion)
                <article class="group overflow-hidden rounded-[2.5rem] border border-white/5 bg-zinc-950/55 shadow-2xl backdrop-blur-md">
                    <div class="flex flex-col xl:flex-row xl:items-stretch">
                        <div class="border-b border-white/5 bg-white/[0.02] p-6 xl:w-[34%] xl:border-b-0 xl:border-r lg:p-8">
                            <div class="mb-6 flex items-center justify-between gap-3">
                                <span class="rounded-full border border-[#7000ff]/20 bg-[#7000ff]/10 px-3 py-1 text-[9px] font-black uppercase tracking-widest text-[#b98cff]">
                                    Sesion vinculada
                                </span>
                                <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[9px] font-black uppercase tracking-widest text-zinc-400">
                                    {{ $sesion['transcripciones_count'] ?? 0 }} transcripciones
                                </span>
                            </div>

                            <h2 class="text-2xl font-black italic uppercase tracking-tight leading-tight text-white transition-colors group-hover:text-[#b98cff] lg:text-3xl">
                                {{ $sesion['titulo'] }}
                            </h2>

                            <div class="mt-4 space-y-3 text-sm text-zinc-400">
                                <div class="flex items-center gap-2">
                                    <span class="min-w-[72px] text-[9px] font-black uppercase tracking-[0.3em] text-zinc-600">Usuario</span>
                                    <span class="font-medium text-zinc-300">{{ $sesion['presentador'] ?? 'Usuario' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="min-w-[72px] text-[9px] font-black uppercase tracking-[0.3em] text-zinc-600">Fecha</span>
                                    <span>{{ $sesion['fecha'] ?? 'Sin fecha' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="min-w-[72px] text-[9px] font-black uppercase tracking-[0.3em] text-zinc-600">Horario</span>
                                    <span>{{ $sesion['hora_inicio'] ?? '--:--' }} a {{ $sesion['hora_fin'] ?? '--:--' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="min-w-[72px] text-[9px] font-black uppercase tracking-[0.3em] text-zinc-600">Idiomas</span>
                                    <span>{{ $sesion['idiomas'] ?? 'Sin idiomas' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="min-w-[72px] text-[9px] font-black uppercase tracking-[0.3em] text-zinc-600">Duracion</span>
                                    <span>{{ number_format((float) ($sesion['duracion_horas'] ?? 0), 2) }} h</span>
                                </div>
                            </div>
                        </div>

                        <div class="xl:flex-1 p-6 lg:p-8 flex flex-col gap-6">
                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-[0.35em] text-zinc-500">Ultimo texto registrado</p>
                                    <p class="mt-2 max-w-4xl text-sm leading-7 text-zinc-200">
                                        {{ !empty($sesion['ultimo_texto']) ? \Illuminate\Support\Str::limit($sesion['ultimo_texto'], 260) : 'Todavia no hay texto capturado para esta sesion.' }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                                <div class="rounded-2xl border border-white/5 bg-white/[0.03] px-4 py-4">
                                    <p class="text-[9px] font-black uppercase tracking-[0.3em] text-zinc-600">Sesion</p>
                                    <p class="mt-2 text-lg font-black text-white">{{ $sesion['titulo'] }}</p>
                                </div>
                                <div class="rounded-2xl border border-white/5 bg-white/[0.03] px-4 py-4">
                                    <p class="text-[9px] font-black uppercase tracking-[0.3em] text-zinc-600">Fecha</p>
                                    <p class="mt-2 text-lg font-black text-white">{{ $sesion['fecha'] ?? 'N/A' }}</p>
                                </div>
                                <div class="rounded-2xl border border-white/5 bg-white/[0.03] px-4 py-4">
                                    <p class="text-[9px] font-black uppercase tracking-[0.3em] text-zinc-600">Idiomas</p>
                                    <p class="mt-2 text-lg font-black text-white">{{ $sesion['idiomas'] ?? 'Sin idiomas' }}</p>
                                </div>
                                <div class="rounded-2xl border border-white/5 bg-white/[0.03] px-4 py-4">
                                    <p class="text-[9px] font-black uppercase tracking-[0.3em] text-zinc-600">Transcripciones</p>
                                    <p class="mt-2 text-lg font-black text-white">{{ $sesion['transcripciones_count'] ?? 0 }}</p>
                                </div>
                            </div>

                            @if(($sesion['extension_count'] ?? 0) > 0)
                                <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                                    <div class="rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-4">
                                        <p class="text-[9px] font-black uppercase tracking-[0.3em] text-emerald-200">Tiempo extra</p>
                                        <p class="mt-2 text-lg font-black text-emerald-100">{{ number_format((float) ($sesion['extra_time_hours'] ?? 0), 2) }} h</p>
                                    </div>
                                    <div class="rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-4">
                                        <p class="text-[9px] font-black uppercase tracking-[0.3em] text-emerald-200">Extensiones</p>
                                        <p class="mt-2 text-lg font-black text-emerald-100">{{ $sesion['extension_count'] ?? 0 }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-4">
                                        <p class="text-[9px] font-black uppercase tracking-[0.3em] text-emerald-200">Ultima extension</p>
                                        <p class="mt-2 text-lg font-black text-emerald-100">
                                            {{ $sesion['last_extended_at'] ? \Illuminate\Support\Carbon::parse($sesion['last_extended_at'])->format('Y-m-d H:i') : 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-[2.5rem] border border-dashed border-white/10 bg-zinc-900/20 p-20 text-center">
                    <p class="text-[10px] font-black uppercase tracking-[0.4em] text-zinc-500">No hay actividad registrada</p>
                    @if(($q ?? '') !== '')
                        <p class="mt-4 text-sm text-zinc-500">No encontramos coincidencias para tu busqueda.</p>
                    @endif
                </div>
            @endforelse
        </div>

        @if(method_exists($sesionesConEstadisticas, 'hasPages') && $sesionesConEstadisticas->hasPages())
            <div class="mt-10 flex justify-center">
                <div class="rounded-[1.75rem] border border-white/10 bg-zinc-950/70 px-5 py-4 shadow-2xl">
                    {{ $sesionesConEstadisticas->onEachSide(1)->links() }}
                </div>
            </div>
        @endif

        <footer class="mt-20 flex flex-col items-center gap-6 opacity-30">
            <div class="h-[1px] w-40 bg-gradient-to-r from-transparent via-zinc-500 to-transparent"></div>
            <p class="text-[9px] font-black uppercase tracking-[0.8em] text-zinc-500">Spikia Control Interface v2.0</p>
        </footer>
    </div>
</div>
@endsection
