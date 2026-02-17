<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transcripcion;
use App\Events\TranscripcionCreada;

class TranscripcionController extends Controller
{
    /**
     * Guardar nueva transcripción y emitir evento en tiempo real
     */
    public function store(Request $request)
    {
        $request->validate([
            'texto' => 'required|string',
            'idioma' => 'required|string',
            'slug' => 'required|string',
        ]);

        $transcripcion = Transcripcion::create([
            'texto' => $request->texto,
            'idioma' => $request->idioma,
        ]);

        // 🔴 Enviar evento por broadcasting
        broadcast(new TranscripcionCreada($transcripcion, $request->slug));

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Obtener última traducción por idioma
     */
    public function ultimaTraduccion($id, $idioma)
    {
        $transcripcion = Transcripcion::where('idioma', $idioma)
            ->latest()
            ->first();

        if (!$transcripcion) {
            return response()->json([
                'texto' => ''
            ]);
        }

        return response()->json([
            'texto' => $transcripcion->texto
        ]);
    }
}
