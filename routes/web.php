<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SesionController; 
use App\Http\Controllers\TranscripcionController;
use App\Http\Controllers\TraduccionController;

/*
|--------------------------------------------------------------------------
| RUTAS DE DATOS (Fuera de auth para evitar bloqueos)
|--------------------------------------------------------------------------
*/

// Obtener última traducción (API)
Route::get('/api/obtener-texto/{id}/{idioma}', [TranscripcionController::class, 'ultimaTraduccion']);

// Guardar transcripción
Route::post('/transcripciones/guardar', [TranscripcionController::class, 'store'])
    ->name('transcripciones.store');

// 🔴 NUEVA RUTA PARA BROADCASTING EN TIEMPO REAL
Route::post('/traducciones', [TraduccionController::class, 'store'])
    ->name('traducciones.store');


/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

Route::get('/', function () { 
    return view('welcome'); 
})->name('home');

Route::get('/reunion/{slug}', [SesionController::class, 'reunion'])
    ->name('sesion.reunion');

Route::get('/master/{slug}', [SesionController::class, 'master'])
    ->name('sesion.master');

Route::get('/transmision/{slug}', [SesionController::class, 'transmision'])
    ->name('sesion.transmision');


/*
|--------------------------------------------------------------------------
| RUTAS PRIVADAS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () { 
        return view('portada'); 
    })->name('dashboard');

    Route::get('/sesiones', [SesionController::class, 'index'])
        ->name('sesiones');

    Route::post('/sesiones', [SesionController::class, 'store'])
        ->name('sesiones.store');

    Route::put('/sesiones/{id}', [SesionController::class, 'update'])
        ->name('sesiones.update');

    Route::delete('/sesiones/{id}', [SesionController::class, 'destroy'])
        ->name('sesiones.destroy');

    Route::get('/actividad', function () { 
        return view('actividad.index'); 
    })->name('actividad');

    Route::get('/glosarios', function () { 
        return view('glosarios.index'); 
    })->name('glosarios');

    Route::get('/transcripciones', function () { 
        return view('transcripciones.index'); 
    })->name('transcripciones');

    Route::get('/comprar', function () { 
        return view('comprar.index'); 
    })->name('comprar');

    Route::get('/soporte', function () { 
        return view('soporte.index'); 
    })->name('soporte');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__.'/auth.php';
