<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// La limpieza BORRA sesiones vencidas. Correrla cada minuto hacia que una demo
// desapareciera en plena presentacion (y todo empezaba a dar 404 "No query results").
// Una vez al dia de madrugada es suficiente: las demos vencidas dejan de usarse igual
// por la validacion demo_expired, pero ya no se borran mientras las tienes abiertas.
Schedule::command('spikia:sessions:cleanup')->dailyAt('04:00');

// Los MP3 de traduccion se acumulan en storage/app/public/traducciones y nunca
// se borraban: el disco crecia sin limite. Limpiamos los de mas de 6h cada hora.
Schedule::command('spikia:audio:cleanup --hours=6')->hourly();
