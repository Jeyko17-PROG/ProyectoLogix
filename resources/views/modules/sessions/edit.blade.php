@extends('layouts.spikia')

@push('styles')
@vite('resources/css/sessions-edit.css')
@endpush

@section('content')
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


