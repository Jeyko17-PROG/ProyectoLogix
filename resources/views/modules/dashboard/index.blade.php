@extends('layouts.spikia')

@section('title', 'Dashboard | Spikia')

@section('content')

<div class="flex min-h-screen text-white font-sans selection:bg-neonBlue/30">

    <aside class="w-64 bg-zinc-950 border-r border-white/10 px-5 py-6 flex flex-col sticky top-0 h-screen overflow-y-auto">
        <div class="flex items-center justify-center px-2 w-full">
            <img src="{{ asset('storage/media/images/spikia-25.png') }}" class="h-24 w-auto object-contain" alt="Spikia">
        </div>

        <div class="mt-10">
            <div class="mb-4 px-1">
                <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-zinc-500">Navegación</h2>
            </div>

            <button
                id="menuToggle"
                type="button"
                class="w-full flex items-center justify-between px-4 py-3 rounded-2xl bg-white/5 border border-white/10 text-white hover:bg-white/10 transition-all duration-200"
                aria-expanded="false"
                aria-controls="menuPanel"
            >
                <span class="text-base font-semibold tracking-wide">Menú</span>
                <span id="menuChevron" class="text-zinc-400 text-lg transition-transform duration-200">v</span>
            </button>

            <div id="menuPanel" class="mt-3 overflow-hidden rounded-[1.5rem] border border-white/10 bg-zinc-900/95 p-3 shadow-[0_20px_80px_rgba(0,0,0,0.45)] backdrop-blur-xl transition-all duration-300 ease-out max-h-0 opacity-0 translate-y-2 scale-[0.98] pointer-events-none">
                <div class="space-y-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center justify-between px-4 py-3 rounded-2xl border transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white border-white/15' : 'bg-white/0 text-zinc-300 border-transparent hover:bg-white/5 hover:text-white hover:border-white/10' }}">
                        <span class="font-semibold text-[15px] tracking-wide">Portada</span>
                        <span class="text-zinc-500">></span>
                    </a>

                    <a href="{{ route('sesiones.index') }}" class="flex items-center justify-between px-4 py-3 rounded-2xl border transition-all duration-200 {{ request()->routeIs('sesiones.*') ? 'bg-white/10 text-white border-white/15' : 'bg-white/0 text-zinc-300 border-transparent hover:bg-white/5 hover:text-white hover:border-white/10' }}">
                        <span class="font-semibold text-[15px] tracking-wide">Sesiones</span>
                        <span class="text-zinc-500">></span>
                    </a>

                    @if(auth()->check() && auth()->user()->email === 'luisgarciab193@gmail.com')
                    <a href="{{ route('actividad.index') }}" class="flex items-center justify-between px-4 py-3 rounded-2xl border transition-all duration-200 {{ request()->routeIs('actividad.*') ? 'bg-white/10 text-white border-white/15' : 'bg-white/0 text-zinc-300 border-transparent hover:bg-white/5 hover:text-white hover:border-white/10' }}">
                        <span class="font-semibold text-[15px] tracking-wide">Log</span>
                        <span class="text-zinc-500">></span>
                    </a>
                    @endif

                    <a href="{{ route('transcripciones.listado') }}" class="flex items-center justify-between px-4 py-3 rounded-2xl border transition-all duration-200 {{ request()->routeIs('transcripciones.*') ? 'bg-white/10 text-white border-white/15' : 'bg-white/0 text-zinc-300 border-transparent hover:bg-white/5 hover:text-white hover:border-white/10' }}">
                        <span class="font-semibold text-[15px] tracking-wide">Transcripciones</span>
                        <span class="text-zinc-500">></span>
                    </a>

                    <a href="{{ route('glosarios') }}" class="flex items-center justify-between px-4 py-3 rounded-2xl border transition-all duration-200 {{ request()->routeIs('glosarios') ? 'bg-white/10 text-white border-white/15' : 'bg-white/0 text-zinc-300 border-transparent hover:bg-white/5 hover:text-white hover:border-white/10' }}">
                        <span class="font-semibold text-[15px] tracking-wide">Glosarios</span>
                        <span class="text-zinc-500">></span>
                    </a>
                </div>

                <div class="pt-4 mt-4 border-t border-white/5 space-y-2">
                    <a href="{{ route('soporte') }}" class="flex items-center justify-between px-4 py-3 rounded-2xl transition-all duration-200 text-zinc-300 bg-white/5 border border-white/10 hover:bg-white/10 hover:text-white hover:border-white/15">
                        <span class="font-semibold text-[15px] tracking-wide">Soporte</span>
                        <span class="text-zinc-500">></span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-between px-4 py-3 rounded-2xl transition-all duration-200 text-red-200 bg-red-500/10 border border-red-500/20 hover:bg-red-500/20 hover:border-red-500/40 hover:text-white">
                            <span class="font-semibold text-[15px] tracking-wide">Cerrar sesión</span>
                            <span class="text-red-300">></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <main class="flex-1 bg-zinc-900 overflow-y-auto">
        
        <div class="flex items-center justify-between px-8 pt-8 pb-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Panel de Control</h1>
                <p class="text-zinc-500 text-sm">Bienvenido de vuelta, {{ explode(' ', auth()->user()->name)[0] }}</p>
            </div>

            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2 px-3 py-2 rounded-full border border-white/10 bg-zinc-800/50 text-zinc-300">
                    <span class="text-xs font-semibold uppercase tracking-[0.25em]">Créditos</span>
                    @if(($creditStats['half_alert'] ?? false))
                        <span class="w-2.5 h-2.5 bg-red-500 rounded-full"></span>
                    @endif
                </div>

                <div class="flex items-center gap-3 bg-zinc-800/50 p-1.5 pr-4 rounded-full border border-white/5">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-spikiaPurple to-neonBlue flex items-center justify-center font-bold text-xs">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="text-sm font-medium">{{ auth()->user()->name }}</span>
                </div>
            </div>
        </div>

        <div class="p-8 max-w-7xl mx-auto">
            
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-12">
                <div class="lg:col-span-3 bg-gradient-to-br from-zinc-800 to-zinc-900 border border-white/10 rounded-[2.5rem] p-10 relative overflow-hidden group">
                    <div class="relative z-10">
                        <span class="bg-spikiaPurple/20 text-spikiaPurple px-4 py-1 rounded-full text-xs font-bold tracking-widest uppercase mb-4 inline-block border border-spikiaPurple/30">
                            Status: Online
                        </span>
                        <h2 class="text-4xl font-extrabold mb-4 leading-tight">
                            Potencia tu voz con <br> <span class="text-transparent bg-clip-text bg-gradient-to-r from-neonBlue to-spikiaPurple">Inteligencia Artificial</span>
                        </h2>
                        <p class="text-zinc-400 max-w-md mb-8 leading-relaxed">Todas tus herramientas de transcripción y análisis listas para el siguiente nivel.</p>
                    </div>
                    <div class="absolute -right-20 -top-20 w-80 h-80 bg-spikiaPurple/10 rounded-full blur-[100px] group-hover:bg-spikiaPurple/20 transition-all duration-700"></div>
                </div>

                <div class="bg-zinc-950 border border-white/10 rounded-[2.5rem] p-8 flex flex-col justify-center relative overflow-hidden">
                    <p class="text-zinc-500 text-sm font-medium mb-1">Créditos de uso</p>
                    <h3 class="text-3xl font-bold mb-3">{{ ($creditStats['unlimited'] ?? false) ? 'Ilimitados' : 'Plan 100' }}</h3>
                    <p class="text-xs uppercase tracking-[0.3em] text-zinc-500 mb-6">
                        @if(($creditStats['unlimited'] ?? false))
                            Cuenta ilimitada
                        @else
                            {{ $creditStats['used'] ?? 0 }}/{{ $creditStats['limit'] ?? 100 }} usados
                        @endif
                    </p>

                    <div class="space-y-4">
                        @if(($creditStats['unlimited'] ?? false))
                            <div class="px-4 py-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-200 text-xs font-semibold">
                                Cuenta ilimitada activa para este usuario.
                            </div>
                        @else
                            <div class="flex justify-between text-xs font-bold uppercase tracking-wider">
                                <span class="text-zinc-400">Progreso</span>
                                <span class="text-neonBlue">{{ $creditStats['percent'] ?? 0 }}%</span>
                            </div>
                            <div class="w-full h-2 bg-zinc-800 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-neonBlue to-spikiaPurple rounded-full" style="width:{{ $creditStats['percent'] ?? 0 }}%"></div>
                            </div>
                            <p class="text-xs text-zinc-500 italic">Restan {{ $creditStats['remaining'] ?? 0 }} créditos</p>
                            @if(($creditStats['half_alert'] ?? false))
                                <div class="px-4 py-3 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-300 text-xs font-semibold">
                                    Aviso: ya se consumio la mitad del plan.
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-16 h-[400px]">
                <div class="md:row-span-2 rounded-3xl overflow-hidden group border border-white/5">
                    <img src="https://images.unsplash.com/photo-1590602847861-f357a9332bbc?auto=format&fit=crop&q=80" 
                        class="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-700 scale-105 group-hover:scale-100">
                </div>
                <div class="md:col-span-2 rounded-3xl overflow-hidden group border border-white/5">
                <img src="https://images.unsplash.com/photo-1589903308914-12911a670564?auto=format&fit=crop&q=80" 
                    class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-all duration-700">
                </div>
                <div class="rounded-3xl overflow-hidden group border border-white/5 bg-spikiaPurple/20 flex items-center justify-center">
                <span class="text-4xl group-hover:scale-125 transition-transform duration-500">✦</span>
                </div>
                <div class="rounded-3xl overflow-hidden group border border-white/5">
                <img src="https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&q=80" 
                class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500">
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 border-t border-white/5 pt-16">
                
                <div>
                    <h2 class="text-2xl font-bold mb-2 text-white">Multimedia</h2>
                    <p class="text-zinc-500 text-sm mb-8">Sube contenido para mostrar en tu portada pública.</p>
                    
                    <form action="{{ route('videos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-zinc-400 uppercase ml-2">Título</label>
                            <input type="text" name="title" placeholder="Ej: Nueva campaña 2024" required
                            class="w-full p-4 rounded-2xl bg-zinc-950 border border-white/10 focus:border-spikiaPurple outline-none transition text-sm">
                        </div>

                        <div class="p-4 rounded-2xl border-2 border-dashed border-white/10 hover:border-spikiaPurple/50 transition bg-zinc-950/50">
                            <p class="text-center text-xs text-zinc-500 mb-2">JPG, PNG o MP4</p>
                            <input type="file" name="image" class="text-xs text-zinc-400 w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-spikiaPurple file:text-white hover:file:bg-neonBlue">
                        </div>

                        <button type="submit"
                                class="w-full py-4 bg-white text-black font-bold rounded-2xl hover:bg-neonBlue hover:text-white transition-all duration-300 shadow-xl shadow-white/5">
                            Publicar Contenido
                        </button>
                    </form>
                </div>

                <div class="lg:col-span-2">
                    @if($videos->count() > 0)
                        <div class="relative rounded-[2rem] overflow-hidden shadow-2xl border border-white/10 bg-black group">
                            <div id="slider" class="flex transition-transform duration-[800ms] cubic-bezier(0.4, 0, 0.2, 1)">
                                @foreach($videos as $video)
                                    <div class="min-w-full relative h-[450px]">
                                        @if($video->path)
                                            <video autoplay muted loop class="w-full h-full object-cover">
                                                <source src="{{ asset('storage/' . $video->path) }}">
                                            </video>
                                        @elseif($video->image)
                                            <img src="{{ asset('storage/' . $video->image) }}" class="w-full h-full object-cover">
                                        @endif

                                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent flex flex-col justify-end p-10">
                                            <h3 class="text-3xl font-bold text-white mb-4">{{ $video->title }}</h3>
                                            
                                            <form action="{{ route('videos.destroy', $video->id) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button class="px-5 py-2 bg-red-500/10 text-red-500 border border-red-500/20 rounded-lg text-xs font-bold hover:bg-red-500 hover:text-white transition">
                                                    ELIMINAR ENTRADA
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="absolute bottom-6 right-10 flex gap-2">
                                @foreach($videos as $index => $v)
                                    <div class="w-8 h-1 bg-white/20 rounded-full overflow-hidden">
                                        <div class="h-full bg-white transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="h-[450px] border-2 border-dashed border-white/5 rounded-[2rem] flex flex-center flex-col items-center justify-center text-zinc-600">
                            <span class="text-5xl mb-4">🖼️</span>
                            <p>No hay contenido en el carrusel aún.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const menuToggle = document.getElementById('menuToggle');
        const menuPanel = document.getElementById('menuPanel');
        const menuChevron = document.getElementById('menuChevron');

        if (menuToggle && menuPanel && menuChevron) {
            menuToggle.addEventListener('click', () => {
                const isOpen = menuPanel.classList.contains('max-h-[900px]');

                if (isOpen) {
                    menuPanel.classList.remove('max-h-[900px]', 'opacity-100', 'translate-y-0', 'scale-100', 'pointer-events-auto');
                    menuPanel.classList.add('max-h-0', 'opacity-0', 'translate-y-2', 'scale-[0.98]', 'pointer-events-none');
                    menuChevron.style.transform = 'rotate(0deg)';
                    menuToggle.setAttribute('aria-expanded', 'false');
                    return;
                }

                menuPanel.classList.remove('max-h-0', 'opacity-0', 'translate-y-2', 'scale-[0.98]', 'pointer-events-none');
                menuPanel.classList.add('max-h-[900px]', 'opacity-100', 'translate-y-0', 'scale-100', 'pointer-events-auto');
                menuChevron.style.transform = 'rotate(180deg)';
                menuToggle.setAttribute('aria-expanded', 'true');
            });
        }

        const slider = document.getElementById('slider');
        if (slider && slider.children.length > 1) {
            let index = 0;
            const total = slider.children.length;

            setInterval(() => {
                index = (index + 1) % total;
                slider.style.transform = `translateX(-${index * 100}%)`;
            }, 6000);
        }
    });
</script>

@endsection


