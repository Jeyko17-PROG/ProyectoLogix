<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Glosario extends Model
{
    protected $table = 'glosarios';

    protected $fillable = [
        'user_id',
        'titulo',
        'idioma',
        'terminos'
    ];

    // Relación con sesiones
    public function sesiones()
    {
        return $this->hasMany(Sesion::class, 'glosario_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

