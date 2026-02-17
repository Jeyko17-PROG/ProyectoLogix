<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transcripcion extends Model
{
    protected $table = 'transcripciones';

    protected $fillable = [
        'texto',
        'idioma',
        'sesion_id'
    ];
}
