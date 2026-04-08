@extends('layouts.spikia')

@section('title', 'Spikia - ' . $sesion->titulo)

@push('styles')
<style>
    body {
        background-color: #0b0b0b;
    }
    .mobile-container {
        width: 100%;
        max-width: 400px;
        padding: 20px;
        text-align: center;
        margin: 0 auto;
    }
    .card {
        background: #121212;
        border: 1px solid #7C3AED;
        border-radius: 20px;
        padding: 40px 20px;
        box-shadow: 0 0 20px rgba(124, 58, 237, 0.2);
    }
    .logo {
        width: 120px;
        margin-bottom: 20px;
    }
    h1 {
        font-size: 1.5rem;
        margin-bottom: 10px;
    }
    p {
        color: #bbb;
        margin-bottom: 30px;
    }
    .btn-assist {
        background: #7C3AED;
        color: white;
        border: none;
        padding: 15px 40px;
        border-radius: 30px;
        font-weight: bold;
        font-size: 1.1rem;
        cursor: pointer;
        width: 100%;
        transition: transform 0.2s;
    }
    .btn-assist:active {
        transform: scale(0.95);
    }
    .btn-lang {
        background: #1a1a1a;
        color: white;
        border: 1px solid #333;
        padding: 15px;
        border-radius: 12px;
        font-size: 1rem;
        cursor: pointer;
        text-align: left;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        width: 100%;
    }
    .btn-lang:hover {
        border-color: #7C3AED;
        background: #222;
    }
    .dot {
        height: 10px;
        width: 10px;
        background-color: #7C3AED;
        border-radius: 50%;
    }
</style>
@endpush

@section('content')
<div class="mobile-container">
    <div id="view-info" class="card">
        <img src="{{ asset('storage/media/images/spikia-25.png') }}" class="logo" alt="Spikia">
        <h1>{{ $sesion->titulo }}</h1>
        <p>{{ $sesion->presentador }}<br>{{ $sesion->fecha_inicio }} | {{ $sesion->hora_inicio }}</p>
        <button id="show-languages-btn" class="btn-assist">ASISTIR</button>
    </div>

    <div id="view-langs" class="card" style="display: none;">
        <h2 style="margin-top: 0;">Selecciona tu idioma</h2>
        <p>Escoge el canal de audio para la interpretación en vivo.</p>
        <div style="display: grid; gap: 12px;">
            @foreach(config('spikia.listener_languages', []) as $language)
                <button class="btn-lang" data-mobile-lang="{{ $language['id'] }}">
                    {{ $language['label'] }}
                    <span class="dot"></span>
                </button>
            @endforeach
        </div>
        <button id="back-btn" style="background: none; border: none; color: #777; margin-top: 20px; cursor: pointer;">← Volver</button>
    </div>
</div>

<script>
    window.__SPIKIA_MOBILE__ = @json([
        'streamBaseUrl' => route('sesion.transmision', ['slug' => $sesion->slug]),
    ]);
</script>
@endsection
