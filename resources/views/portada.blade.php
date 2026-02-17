@extends('layouts.spikia')

@section('title', 'Dashboard')

@section('content')

<div class="min-h-screen bg-gradient-to-br from-black via-zinc-900 to-black text-white">

    <!-- HEADER / NAVBAR -->
    <header class="fixed top-0 left-0 right-0 z-40
                   bg-gradient-to-r from-black via-zinc-900 to-black
                   border-b border-white/10">
        <div class="flex items-center justify-between px-8 py-4">

            <!-- LOGO + BIENVENIDA -->
            <div class="flex items-center gap-4">
                <img
                    src="{{ asset('images/spikia-25.png') }}"
                    alt="Spikia"
                    class="h-12 drop-shadow-[0_0_25px_rgba(124,58,237,0.6)]"
                >

                <h1 class="text-xl font-semibold">
                    👋 Bienvenido, {{ Auth::user()->name ?? 'Luis' }}
                </h1>
            </div>

            <!-- BOTÓN MENÚ -->
            <button onclick="toggleMenu()"
                class="bg-zinc-800 hover:bg-zinc-700
                       px-4 py-2 rounded-lg transition
                       flex items-center gap-2">
                ☰ Menú
            </button>
        </div>
    </header>

    <!-- PANEL DESPLEGABLE -->
    <div id="menuPanel"
        class="fixed top-0 right-0 h-full w-72
               bg-zinc-950 shadow-xl
               transform translate-x-full
               transition-transform duration-300
               z-50">

        <div class="p-6 border-b border-white/10 flex justify-between items-center">
            <h2 class="text-lg font-semibold">📂 Navegación</h2>
            <button onclick="toggleMenu()" class="text-gray-400 hover:text-white">✖</button>
        </div>

        <nav class="flex flex-col p-4 gap-1">

            <a href="{{ route('dashboard') }}" class="menu-item">🏠 Portada</a>
            <a href="{{ route('sesiones') }}" class="menu-item">🎤 Sesiones</a>
            <a href="{{ route('actividad') }}" class="menu-item">📊 Actividad</a>
            <a href="{{ route('transcripciones') }}" class="menu-item">📝 Transcripciones</a>
            <a href="{{ route('glosarios') }}" class="menu-item">📚 Glosarios</a>
            <a href="{{ route('soporte') }}" class="menu-item">🛟 Soporte</a>

            <form method="POST" action="{{ route('logout') }}"
                  class="mt-4 border-t border-white/10 pt-4">
                @csrf
                <button type="submit"
                    class="w-full text-left text-red-400
                           hover:bg-red-500/10
                           px-4 py-3 rounded-lg">
                    🚪 Cerrar sesión
                </button>
            </form>

        </nav>
    </div>

    <!-- CONTENIDO -->
    <main class="pt-28">

        <!-- CARRUSEL -->
        <div class="relative overflow-hidden rounded-xl shadow-lg mx-8 mb-12">
            <div class="flex animate-[slide_18s_linear_infinite]">
                <img src="https://picsum.photos/1200/400?1" class="w-full object-cover">
                <img src="https://picsum.photos/1200/400?2" class="w-full object-cover">
                <img src="https://picsum.photos/1200/400?3" class="w-full object-cover">
            </div>
        </div>

        <!-- VIDEOS -->
        <div class="px-8">
            <h2 class="text-2xl font-semibold mb-4">🎥 Videos destacados</h2>

            <div class="grid md:grid-cols-3 gap-6 mb-12">
                <iframe class="w-full h-64 rounded-lg"
                    src="https://www.youtube.com/embed/dQw4w9WgXcQ" allowfullscreen></iframe>

                <iframe class="w-full h-64 rounded-lg"
                    src="https://www.youtube.com/embed/9bZkp7q19f0" allowfullscreen></iframe>

                <iframe class="w-full h-64 rounded-lg"
                    src="https://www.youtube.com/embed/l482T0yNkeo" allowfullscreen></iframe>
            </div>
        </div>

    </main>
</div>

<!-- ESTILOS -->
<style>
    .menu-item {
        padding: 12px 16px;
        border-radius: 8px;
        transition: .2s;
    }

    .menu-item:hover {
        background: rgba(255,255,255,.08);
    }
</style>

<!-- SCRIPT -->
<script>
    function toggleMenu() {
        const panel = document.getElementById('menuPanel');
        panel.classList.toggle('translate-x-full');
    }
</script>

@endsection
