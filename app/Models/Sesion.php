<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sesion extends Model
{
    protected $table = 'sesiones';

    protected $fillable = [
        'titulo', 
        'presentador', 
        'cuenta', 
        'fecha_inicio', 
        'hora_inicio', 
        'zoom_link', 
        'idiomas', 
        'subtitulos', 
        'slug'
    ];

    // Esto ayuda a que Laravel maneje los JSON automáticamente
    protected $casts = [
        'idiomas' => 'array',
        'subtitulos' => 'array',
    ];
}