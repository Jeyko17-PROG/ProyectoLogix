@extends('layouts.spikia')

@push('styles')
@vite('resources/css/sessions-edit.css')
@endpush

@section('content')
@php
    $tsConfig = config('spikia.translation_simultaneous', []);
    $sessionTranslation = array_merge([
        'translation_mode' => 'voice_to_voice',
        'speech_to_text_model' => $tsConfig['speech_to_text_model'] ?? 'gpt-4o-mini-transcribe',
        'translation_model' => $tsConfig['translation_model'] ?? 'gpt-5.4-mini',
        'text_to_speech_model' => $tsConfig['text_to_speech_model'] ?? 'gpt-4o-mini-tts',
        'voice' => $tsConfig['voice'] ?? 'marin',
        'audio_delivery_mode' => $tsConfig['audio_delivery_mode'] ?? 'ultra_fast',
        'master_translation_prompt' => $tsConfig['master_translation_prompt'] ?? '',
    ], is_array($sesion->translation_settings ?? null) ? $sesion->translation_settings : []);
@endphp
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

            <label class="label-mini">Modo de Traducción</label>
            <div class="idiomas-grid">
                <label class="idioma-item"><input type="radio" name="translation_mode" value="voice_to_text" {{ ($sessionTranslation['translation_mode'] ?? '') === 'voice_to_text' ? 'checked' : '' }}> Voz a texto</label>
                <label class="idioma-item"><input type="radio" name="translation_mode" value="voice_to_voice" {{ ($sessionTranslation['translation_mode'] ?? '') === 'voice_to_voice' ? 'checked' : '' }}> Voz a voz</label>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label class="label-mini">Modelo STT</label>
                    <select name="speech_to_text_model" class="select-spikia">
                        @foreach(($tsConfig['available_stt_models'] ?? []) as $model)
                            <option value="{{ $model['value'] }}" {{ ($sessionTranslation['speech_to_text_model'] ?? '') === $model['value'] ? 'selected' : '' }}>{{ $model['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label-mini">Modelo de Traducción</label>
                    <select name="translation_model" class="select-spikia">
                        @foreach(($tsConfig['available_translation_models'] ?? []) as $model)
                            <option value="{{ $model['value'] }}" {{ ($sessionTranslation['translation_model'] ?? '') === $model['value'] ? 'selected' : '' }}>{{ $model['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label-mini">Modelo de Voz</label>
                    <select name="text_to_speech_model" class="select-spikia">
                        <option value="{{ $tsConfig['text_to_speech_model'] ?? 'gpt-4o-mini-tts' }}">{{ $tsConfig['text_to_speech_model'] ?? 'gpt-4o-mini-tts' }}</option>
                    </select>
                </div>
                <div>
                    <label class="label-mini">Voz</label>
                    <select name="voice" class="select-spikia">
                        @foreach(($tsConfig['available_voices'] ?? []) as $voice)
                            <option value="{{ $voice }}" {{ ($sessionTranslation['voice'] ?? '') === $voice ? 'selected' : '' }}>{{ $voice }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="grid-column: 1 / -1;">
                    <label class="label-mini">Prioridad de audio</label>
                    <select name="audio_delivery_mode" class="select-spikia">
                        @foreach(($tsConfig['available_audio_delivery_modes'] ?? []) as $mode)
                            <option value="{{ $mode['value'] }}" {{ ($sessionTranslation['audio_delivery_mode'] ?? '') === $mode['value'] ? 'selected' : '' }}>{{ $mode['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <label class="label-mini">Prompt Maestro</label>
            <textarea name="master_translation_prompt" class="input-spikia" rows="5">{{ $sessionTranslation['master_translation_prompt'] ?? '' }}</textarea>

            @if(config('spikia.features.sign_avatar'))
                <label class="idioma-item" style="margin-top: 20px;">
                    <input type="checkbox" name="has_sign_avatar" value="1" {{ $sesion->has_sign_avatar ? 'checked' : '' }}>
                    Avatar 3D en Lengua de Señas (beta) — solo para esta sala
                </label>
            @endif

            <button type="submit" class="btn-save">ACTUALIZAR CONFIGURACIÓN COMPLETA</button>
        </form>
    </div>
</div>
@endsection
