@extends('layouts.spikia')

@section('content')
<style>
    .main-wrapper { padding: 40px; background-color: #050505; min-height: 100vh; color: white; font-family: 'Inter', sans-serif; }
    .brand-header { display: flex; justify-content: center; margin-bottom: 50px; }
    .logo-main { height: 70px; filter: drop-shadow(0 0 15px rgba(124, 58, 237, 0.4)); }
    
    .btn-back { text-decoration: none; display: flex; align-items: center; gap: 8px; color: #6366f1; font-weight: 900; font-size: 10px; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 30px; transition: 0.3s; }
    .btn-back:hover { color: white; transform: translateX(-5px); }

    .edit-container { background: #0a0a0a; border: 1px solid #1a1a1a; margin: 0 auto; border-radius: 40px; width: 100%; max-width: 650px; padding: 50px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.7); }
    .edit-container h2 { font-weight: 900; text-transform: uppercase; font-style: italic; margin-bottom: 30px; font-size: 24px; color: white; letter-spacing: -1px; }
    
    .label-mini { font-size: 9px; font-weight: 900; color: #444; margin-bottom: 10px; text-transform: uppercase; display: block; letter-spacing: 0.1em; }
    
    .input-spikia, .select-spikia { 
        width: 100%; background: #000; color: #fff; border: 1px solid #222; padding: 18px; border-radius: 15px; margin-bottom: 25px; font-weight: bold; font-size: 14px; transition: 0.3s;
    }
    .input-spikia:focus { border-color: #6366f1; outline: none; box-shadow: 0 0 15px rgba(99, 102, 241, 0.2); }

    .btn-save { width: 100%; background: #6366f1; color: white; padding: 20px; border-radius: 20px; font-weight: 900; font-size: 12px; text-transform: uppercase; letter-spacing: 0.2em; border: none; cursor: pointer; transition: 0.4s; margin-top: 20px; }
    .btn-save:hover { background: #4f46e5; transform: translateY(-2px); box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4); }

    .idiomas-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; background: #000; padding: 20px; border-radius: 15px; border: 1px solid #222; margin-bottom: 25px; }
    .idioma-item { display: flex; align-items: center; gap: 10px; font-size: 11px; color: #aaa; font-weight: bold; text-transform: uppercase; }
    .idioma-item input { accent-color: #6366f1; width: 16px; height: 16px; }
</style>

<div class="main-wrapper">
    <div class="brand-header">
        <img src="{{ asset('storage/media/images/spikia-25.png') }}" class="logo-main">
    </div>

    <a href="{{ route('sesiones.index') }}" class="btn-back"> ← Volver al listado </a>

    <div class="edit-container">
        <h2>Editar Sesión: {{ $sesion->titulo }}</h2>
        
        <form action="{{ route('sesiones.update', $sesion->id) }}" method="POST">
            @csrf
            @method('PUT')

            <label class="label-mini">Título de la Sesión</label>
            <input type="text" name="titulo" value="{{ $sesion->titulo }}" class="input-spikia" required>
            
            <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 20px;">
                <div>
                    <label class="label-mini">Fecha del Evento</label>
                    <input type="date" name="fecha_inicio" value="{{ $sesion->fecha_inicio }}" class="input-spikia" required>
                </div>
                <div>
                    <label class="label-mini">Hora Inicio</label>
                    <input type="time" name="hora_inicio" value="{{ $sesion->hora_inicio }}" class="input-spikia" required>
                </div>
                <div>
                    <label class="label-mini">Hora Término</label>
                    <input type="time" name="hora_fin" value="{{ $sesion->hora_fin }}" class="input-spikia">
                </div>
            </div>

            <label class="label-mini">Glosario Especializado Seleccionado</label>
            <select name="glosario_id" class="select-spikia">
                <option value="">Ninguno (Voz Estándar)</option>
                @foreach($glosarios as $g)
                    <option value="{{ $g->id }}" {{ $sesion->glosario_id == $g->id ? 'selected' : '' }}>
                        {{ $g->titulo }}
                    </option>
                @endforeach
            </select>

            <label class="label-mini">Idiomas de Traducción Activos</label>
            <div class="idiomas-grid">
                @php $idiomas_actuales = is_array($sesion->idiomas) ? $sesion->idiomas : []; @endphp
                <label class="idioma-item"><input type="checkbox" name="idiomas[]" value="es-ES" {{ in_array('es-ES', $idiomas_actuales) ? 'checked' : '' }}> Español España</label>
                <label class="idioma-item"><input type="checkbox" name="idiomas[]" value="es-419" {{ in_array('es-419', $idiomas_actuales) ? 'checked' : '' }}> Español LatAm</label>
                <label class="idioma-item"><input type="checkbox" name="idiomas[]" value="en" {{ in_array('en', $idiomas_actuales) ? 'checked' : '' }}> Inglés</label>
                <label class="idioma-item"><input type="checkbox" name="idiomas[]" value="fr" {{ in_array('fr', $idiomas_actuales) ? 'checked' : '' }}> Francés</label>
                <label class="idioma-item"><input type="checkbox" name="idiomas[]" value="pt" {{ in_array('pt', $idiomas_actuales) ? 'checked' : '' }}> Portugués</label>
                <label class="idioma-item"><input type="checkbox" name="idiomas[]" value="de" {{ in_array('de', $idiomas_actuales) ? 'checked' : '' }}> Alemán</label>
                <label class="idioma-item"><input type="checkbox" name="idiomas[]" value="it" {{ in_array('it', $idiomas_actuales) ? 'checked' : '' }}> Italiano</label>
                <label class="idioma-item"><input type="checkbox" name="idiomas[]" value="es" {{ in_array('es', $idiomas_actuales) ? 'checked' : '' }}> Español</label>
            </div>

            <button type="submit" class="btn-save">ACTUALIZAR CONFIGURACIÓN COMPLETA</button>
        </form>
    </div>
</div>
@endsection



