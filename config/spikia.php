<?php

return [
    // Interruptores de modulos opcionales: cada uno debe poder apagarse sin tocar
    // el resto del pipeline. Si esta en false, el codigo que depende de el ni
    // siquiera debe evaluarse (ver guards con config('spikia.features.x') && ...).
    'features' => [
        'sign_avatar' => (bool) env('ENABLE_SIGN_AVATAR', false),
        'meeting_bot' => (bool) env('ENABLE_MEETING_BOT', false),
    ],

    'demo_duration_minutes' => 20,
    'master_base_url' => env('SPIKIA_MASTER_BASE_URL', env('APP_URL', 'http://localhost:8000')),
    'public_base_url' => env('SPIKIA_PUBLIC_BASE_URL', ''),
    'voice_provider' => env('SPIKIA_VOICE_PROVIDER', 'elevenlabs'),
    'socket_enabled' => (bool) env('SPIKIA_SOCKET_ENABLED', false),
    'socket_url' => env('SPIKIA_SOCKET_URL'),
    // Worker Node.js separado (ver /meet-bot) que entra a una reunion de Meet/Zoom como
    // invitado y captura su audio. Vive fuera de PHP porque necesita un navegador Chrome
    // persistente por reunion; Laravel solo lo orquesta via esta URL interna.
    'meeting_bot' => [
        'url' => env('SPIKIA_MEETBOT_URL', 'http://127.0.0.1:4100'),
        'secret' => env('SPIKIA_MEETBOT_SECRET'),
    ],
    'elevenlabs' => [
        'enabled' => (bool) env('ELEVENLABS_ENABLED', false),
        'api_key' => env('ELEVENLABS_API_KEY'),
        'voice_id' => env('ELEVENLABS_VOICE_ID'),
        'male_voice_id' => env('ELEVENLABS_VOICE_ID_MALE'),
        'female_voice_id' => env('ELEVENLABS_VOICE_ID_FEMALE'),
        'model_id' => env('ELEVENLABS_MODEL_ID', 'eleven_turbo_v2'),
        'stability' => (float) env('ELEVENLABS_STABILITY', 0.45),
        'similarity_boost' => (float) env('ELEVENLABS_SIMILARITY_BOOST', 0.75),
        'style' => (float) env('ELEVENLABS_STYLE', 0),
        'use_speaker_boost' => (bool) env('ELEVENLABS_USE_SPEAKER_BOOST', true),
    ],
    'default_license_plan' => 'free',
    'license_plans' => [
        'free' => [
            'label' => 'Gratis',
            'credits' => 100,
            'badge' => '100 tokens',
            'description' => 'Acceso base para probar la plataforma.',
        ],
        'medium' => [
            'label' => 'Media',
            'credits' => 250,
            'badge' => '250 tokens',
            'description' => 'Para uso frecuente con más capacidad.',
        ],
        'premium' => [
            'label' => 'Premium',
            'credits' => 500,
            'badge' => '500 tokens',
            'description' => 'Para operación intensiva y sesiones largas.',
        ],
    ],
    'master_languages' => [
        ['id' => 'en-US', 'base' => 'en', 'speech' => 'en-US', 'name' => 'English'],
        ['id' => 'pt-BR', 'base' => 'pt', 'speech' => 'pt-BR', 'name' => 'Portugués'],
        ['id' => 'it-IT', 'base' => 'it', 'speech' => 'it-IT', 'name' => 'Italiano'],
        ['id' => 'fr-FR', 'base' => 'fr', 'speech' => 'fr-FR', 'name' => 'Francés'],
        ['id' => 'es-ES', 'base' => 'es', 'speech' => 'es-ES', 'name' => 'Español España'],
        ['id' => 'es-419', 'base' => 'es', 'speech' => 'es-MX', 'name' => 'Español LatAm'],
    ],
    'listener_languages' => [
        ['id' => 'en', 'label' => 'ENG'],
        ['id' => 'es-ES', 'label' => 'ESP-ES'],
        ['id' => 'es-419', 'label' => 'ESP-LAT'],
        ['id' => 'pt', 'label' => 'POR'],
        ['id' => 'it', 'label' => 'ITA'],
        ['id' => 'fr', 'label' => 'FRA'],
    ],
    'default_targets' => ['en', 'pt', 'it', 'fr'],

    'translation_simultaneous' => [
        'speech_to_text_model'     => 'gpt-4o-mini-transcribe',
        'translation_model'        => 'gpt-4o-mini',
        'translation_premium_model' => 'gpt-4o',
        'text_to_speech_model'     => 'gpt-4o-mini-tts',
        'voice_provider'           => env('SPIKIA_VOICE_PROVIDER', 'elevenlabs'),
        'voice_gender_profile'     => 'female',
        'voice'                    => 'marin',
        'audio_delivery_mode'      => 'ultra_fast',
        'master_translation_prompt' => 'Eres un traductor médico simultáneo. Traduce TODO el texto recibido de forma literal y COMPLETA, sin resumir, sin omitir palabras, sin acortar oraciones. Conserva muletillas, nombres propios, tecnicismos médicos, números, dosis y unidades exactamente como aparecen. Si el texto tiene errores de transcripción interpreta lo más fielmente posible. Responde SOLO con la traducción, sin comentarios, sin explicaciones, sin prefijos como "Traducción:".',
        'voice_profiles' => [
            ['value' => 'marin', 'label' => 'Marin', 'gender' => 'female', 'voice_id' => env('ELEVENLABS_VOICE_ID_MARIN', env('ELEVENLABS_VOICE_ID_FEMALE'))],
            ['value' => 'coral', 'label' => 'Coral', 'gender' => 'female', 'voice_id' => env('ELEVENLABS_VOICE_ID_CORAL', env('ELEVENLABS_VOICE_ID_FEMALE'))],
            ['value' => 'shimmer', 'label' => 'Shimmer', 'gender' => 'female', 'voice_id' => env('ELEVENLABS_VOICE_ID_SHIMMER', env('ELEVENLABS_VOICE_ID_FEMALE'))],
            ['value' => 'cedar', 'label' => 'Cedar', 'gender' => 'male', 'voice_id' => env('ELEVENLABS_VOICE_ID_CEDAR', env('ELEVENLABS_VOICE_ID_MALE'))],
            ['value' => 'alloy', 'label' => 'Alloy', 'gender' => 'male', 'voice_id' => env('ELEVENLABS_VOICE_ID_ALLOY', env('ELEVENLABS_VOICE_ID_MALE'))],
            ['value' => 'sage', 'label' => 'Sage', 'gender' => 'male', 'voice_id' => env('ELEVENLABS_VOICE_ID_SAGE', env('ELEVENLABS_VOICE_ID_MALE'))],
        ],
        'available_stt_models' => [
            ['label' => 'Estándar — gpt-4o-mini-transcribe',       'value' => 'gpt-4o-mini-transcribe'],
            ['label' => 'Premium — gpt-4o-transcribe',              'value' => 'gpt-4o-transcribe'],
            ['label' => 'Varios hablantes — gpt-4o-transcribe-diarize', 'value' => 'gpt-4o-transcribe-diarize'],
            ['label' => 'Compatibilidad — whisper-1',               'value' => 'whisper-1'],
        ],
        'available_translation_models' => [
            ['label' => 'Estándar — gpt-4o-mini', 'value' => 'gpt-4o-mini'],
            ['label' => 'Premium — gpt-4o',        'value' => 'gpt-4o'],
        ],
        'available_voices' => ['marin', 'coral', 'shimmer', 'cedar', 'alloy', 'sage'],
        'available_audio_delivery_modes' => [
            ['label' => 'Ultra rapido — voz inmediata del navegador', 'value' => 'ultra_fast'],
            ['label' => 'Premium — prioriza voz remota de mayor calidad', 'value' => 'premium'],
        ],
        'available_languages' => [
            ['label' => 'Español',   'value' => 'es'],
            ['label' => 'Inglés',    'value' => 'en'],
            ['label' => 'Portugués', 'value' => 'pt'],
            ['label' => 'Francés',   'value' => 'fr'],
            ['label' => 'Italiano',  'value' => 'it'],
            ['label' => 'Alemán',    'value' => 'de'],
        ],
    ],
];
