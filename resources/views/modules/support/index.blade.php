@extends('layouts.spikia')

@section('title', 'Soporte | Spikia')

@section('content')
<div class="min-h-screen bg-[#050505] text-white">
    <div class="max-w-5xl mx-auto px-8 py-12">
        <div class="flex items-center gap-4 mb-10">
            <a href="{{ route('dashboard') }}" class="group flex items-center justify-center w-12 h-12 rounded-2xl bg-white/5 border border-white/10 hover:bg-[#00d2ff] hover:border-[#00d2ff] transition-all shadow-lg">
                <svg class="w-5 h-5 text-zinc-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-4xl font-black italic tracking-tighter uppercase leading-none">
                    Centro de <span class="text-[#00d2ff]">Soporte</span>
                </h1>
                <p class="text-zinc-500 text-[10px] font-black uppercase tracking-[0.3em] mt-2 italic">Ayuda rapida para operar la plataforma</p>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <div class="md:col-span-2 bg-zinc-900/40 border border-white/10 rounded-[2rem] p-8 backdrop-blur-sm">
                <h2 class="text-xl font-bold mb-4">Que hacer si algo falla</h2>
                <ul class="space-y-3 text-zinc-300 text-sm leading-6">
                    <li>1. Verifica que la sesion este creada y tenga un <code class="text-[#00d2ff]">slug</code> valido.</li>
                    <li>2. Revisa que las rutas de transcripcion y glosarios esten cargando sin errores.</li>
                    <li>3. Si la transmision no responde, recarga la pagina y confirma que la sesion siga activa.</li>
                    <li>4. Si usas despliegue en Linux, mantén las carpetas y vistas en minusculas para evitar problemas de mayusculas/minusculas.</li>
                </ul>
            </div>

            <div class="bg-zinc-900/40 border border-white/10 rounded-[2rem] p-8 backdrop-blur-sm">
                <h2 class="text-xl font-bold mb-4">Accesos utiles</h2>
                <div class="space-y-3 text-sm">
                    <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 transition">Ir al panel</a>
                    <a href="{{ route('sesiones.index') }}" class="block px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 transition">Gestionar sesiones</a>
                    <a href="{{ route('transcripciones.listado') }}" class="block px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 transition">Ver transcripciones</a>
                    <a href="{{ route('glosarios') }}" class="block px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 transition">Abrir glosarios</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection