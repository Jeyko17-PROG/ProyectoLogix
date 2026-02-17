@extends('layouts.spikia')

@section('title', 'Dashboard | Spikia')

@section('content')

<div class="flex min-h-screen text-white">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-zinc-950 border-r border-white/10 px-5 py-6 flex flex-col">

        <!-- LOGO -->
        <div class="flex items-center gap-3 mb-10">
            <img src="{{ asset('images/spikia-25.png') }}" class="h-10">
            <span class="font-bold text-lg tracking-wide">SPIKIA</span>
        </div>

        <!-- MENU -->
        <nav class="space-y-2 text-sm flex-1">

            @php
                $menu = [
                    ['icon' => '🏠', 'label' => 'Portada', 'route' => 'dashboard'],
                    ['icon' => '🎤', 'label' => 'Sesiones', 'route' => 'sesiones'],
                    ['icon' => '📊', 'label' => 'Actividad', 'route' => 'actividad'],
                    ['icon' => '📝', 'label' => 'Transcripciones', 'route' => 'transcripciones'],
                    ['icon' => '📚', 'label' => 'Glosarios', 'route' => 'glosarios'],
                    ['icon' => '💳', 'label' => 'Comprar', 'route' => 'comprar'],
                    ['icon' => '🛟', 'label' => 'Soporte', 'route' => 'soporte'],
                ];
            @endphp

            @foreach($menu as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl
                          hover:bg-white/5 transition">
                    <span>{{ $item['icon'] }}</span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach

        </nav>

        <!-- LOGOUT ABAJO -->
        <form method="POST" action="{{ route('logout') }}" class="mt-6">
            @csrf
            <button type="submit"
                class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl
                       text-red-400 hover:text-red-500 hover:bg-white/5 transition">
                🚪 <span>Cerrar sesión</span>
            </button>
        </form>

    </aside>

    <!-- CONTENIDO -->
    <main class="flex-1 bg-zinc-900 p-8">

        <!-- TOPBAR -->
        <div class="flex items-center justify-between mb-10">

            <h1 class="text-2xl font-bold">Dashboard</h1>

            <div class="flex items-center gap-4">

                <!-- NOTIFICACIONES -->
                <button class="relative hover:scale-110 transition">
                    🔔
                    <span class="absolute -top-2 -right-2 bg-red-500 text-xs rounded-full px-1">
                        3
                    </span>
                </button>

                <!-- USUARIO -->
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-full bg-spikiaPurple flex items-center justify-center font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="text-sm">{{ auth()->user()->name }}</span>
                </div>

            </div>

        </div>

        <!-- TARJETAS -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">

            <!-- PLAN -->
            <div class="bg-zinc-950 rounded-2xl p-5 border border-white/10">
                <p class="text-xs text-zinc-400 mb-2">Tu plan</p>
                <h2 class="text-xl font-semibold text-neonBlue">Activo</h2>
                <p class="text-sm mt-2">Renovación: 30 días</p>
            </div>

            <!-- MINUTOS -->
            <div class="bg-zinc-950 rounded-2xl p-5 border border-white/10">
                <p class="text-xs text-zinc-400 mb-2">Minutos disponibles</p>
                <h2 class="text-xl font-semibold">120 min</h2>
                <div class="w-full h-2 bg-zinc-800 rounded-full mt-3">
                    <div class="h-full bg-neonPink rounded-full" style="width:70%"></div>
                </div>
            </div>

            <!-- ÚLTIMA SESIÓN -->
            <div class="bg-zinc-950 rounded-2xl p-5 border border-white/10">
                <p class="text-xs text-zinc-400 mb-2">Última sesión</p>
                <h2 class="text-xl font-semibold">Evento Global 2026</h2>
                <p class="text-sm mt-2 text-green-400">Finalizada</p>
            </div>

            <!-- ACTIVIDAD -->
            <div class="bg-zinc-950 rounded-2xl p-5 border border-white/10">
                <p class="text-xs text-zinc-400 mb-2">Actividad hoy</p>
                <h2 class="text-xl font-semibold">3 sesiones</h2>
                <p class="text-sm mt-2">+12 usuarios conectados</p>
            </div>

        </div>

        <!-- ACCESOS RÁPIDOS -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <a class="bg-gradient-to-r from-spikiaPurple to-neonBlue
                      rounded-2xl p-6 hover:scale-105 transition cursor-pointer">
                🚀 Crear sesión
            </a>

            <a class="bg-gradient-to-r from-neonPink to-spikiaPurple
                      rounded-2xl p-6 hover:scale-105 transition cursor-pointer">
                📊 Ver actividad
            </a>

            <a class="bg-gradient-to-r from-neonBlue to-neonPink
                      rounded-2xl p-6 hover:scale-105 transition cursor-pointer">
                📚 Administrar glosarios
            </a>

        </div>

    </main>

</div>

@endsection
