<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sesion extends Model
{
    protected $table = 'sesiones';

    protected $fillable = [
        'user_id',
        'titulo',
        'presentador',
        'cuenta',
        'fecha_inicio',
        'hora_inicio',
        'hora_fin',
        'zoom_link',
        'idiomas',
        'subtitulos',
        'slug',
        'glosario_id',
        'idioma_activo',
        'grabacion_url',
    ];

    protected $casts = [
        'idiomas' => 'array',
        'subtitulos' => 'array',
    ];

    public function glosario()
    {
        return $this->belongsTo(Glosario::class, 'glosario_id');
    }

    public function transcripciones()
    {
        return $this->hasMany(Transcripcion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}