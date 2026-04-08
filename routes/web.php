<?php

use App\Modules\Activity\Controllers\ActividadController;
use App\Modules\Billing\Controllers\CreditoController;
use App\Modules\Dashboard\Controllers\DashboardController;
use App\Modules\Glossary\Controllers\GlosarioController;
use App\Modules\Media\Controllers\VideoController;
use App\Modules\Profile\Controllers\ProfileController;
use App\Modules\Sessions\Controllers\SesionController;
use App\Modules\Support\Controllers\SupportController;
use App\Modules\Transcriptions\Controllers\TranscripcionController;
use App\Modules\Traduccion\Controllers\TraduccionController; //
use Illuminate\Support\Facades\Route;

// --- RUTAS PÚBLICAS O SEMI-PÚBLICAS ---
Route::get('/', fn () => view('welcome'))->name('home');

// Estas rutas deben ser accesibles para que el Listener no falle
Route::get('/sesiones/{slug}/transmision', [SesionController::class, 'transmision'])->name('sesion.transmision');
Route::get('/sesiones/{slug}/movil', [SesionController::class, 'movil'])->name('sesion.movil');
Route::get('/sesiones/{slug}/mensajes', [SesionController::class, 'feed'])->name('sesiones.mensajes.feed');

// [CRÍTICO] Ruta de traducciones fuera del middleware auth para pruebas iniciales
Route::post('/traducciones', [TraduccionController::class, 'store'])->name('traducciones.store');

// --- RUTAS PROTEGIDAS ---
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Sesión Master y Control
    Route::get('/sesiones/{slug}/master', [SesionController::class, 'master'])->name('sesion.master');
    Route::get('/sesiones/{slug}/reunion', [SesionController::class, 'reunion'])->name('sesion.reunion');
    
    // Transcripciones
    Route::get('/transcripciones-historial', [TranscripcionController::class, 'listado'])->name('transcripciones.listado');
    Route::get('/transcripcion/{slug}', [TranscripcionController::class, 'index'])->name('transcripcion.index');
    Route::post('/transcripciones/guardar', [TranscripcionController::class, 'store'])->name('transcripciones.store');
    Route::post('/transcripciones/grabacion', [TranscripcionController::class, 'grabarSesion'])->name('transcripciones.grabacion');
    Route::get('/transcripcion/descargar/{slug}/{tipo}/{idioma?}', [TranscripcionController::class, 'descargar'])->name('transcripcion.descargar');

    // Gestión de Sesiones
    Route::resource('sesiones', SesionController::class);
    Route::post('/sesiones/{id}/activar-idioma', [SesionController::class, 'activarIdioma'])->name('sesiones.activarIdioma');
    Route::post('/sesiones/{slug}/mensajes', [SesionController::class, 'publicarMensaje'])->name('sesiones.mensajes.store');

    // Otros Módulos
    Route::post('/creditos/consumir', [CreditoController::class, 'consume'])->name('creditos.consume');
    Route::get('/glosarios', [GlosarioController::class, 'index'])->name('glosarios');
    Route::post('/glosarios/guardar', [GlosarioController::class, 'store'])->name('glosarios.store');
    Route::delete('/glosarios/{id}', [GlosarioController::class, 'destroy'])->name('glosarios.destroy');
    Route::put('/glosarios/{id}', [GlosarioController::class, 'update'])->name('glosarios.update');
    Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
    Route::delete('/videos/{id}', [VideoController::class, 'destroy'])->name('videos.destroy');

    // Perfil y Actividad
    Route::get('/actividad/acceso', [ActividadController::class, 'promptPin'])->name('actividad.pin');
    Route::post('/actividad/acceso', [ActividadController::class, 'verifyPin'])->name('actividad.pin.verify');
    Route::get('/actividad', [ActividadController::class, 'index'])->name('actividad.index');
    Route::get('/actividad/exportar', [ActividadController::class, 'exportarExcel'])->name('actividad.export');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/soporte', [SupportController::class, 'index'])->name('soporte');
});

require __DIR__ . '/auth.php';
