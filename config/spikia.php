<?php

return [
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
];
