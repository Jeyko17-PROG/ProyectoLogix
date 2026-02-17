<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sesion;
use App\Models\Traduccion;
use App\Events\TraduccionGenerada; // 👈 IMPORTANTE

class TraduccionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'slug' => 'required|string',
            'texto_original' => 'required|string',
            'texto_traducido' => 'required|string',
        ]);

        $sesion = Sesion::where('slug', $request->slug)->first();

        if (!$sesion) {
            return response()->json(['error' => 'Sesión no encontrada'], 404);
        }

        // Guardar en BD
        $traduccion = Traduccion::create([
            'sesion_id' => $sesion->id,
            'texto_original' => $request->texto_original,
            'texto_traducido' => $request->texto_traducido,
        ]);

        // 🔥 AQUÍ ESTÁ LO QUE TE FALTA
        event(new TraduccionGenerada(
            $sesion->slug,
            $request->texto_original,
            $request->texto_traducido
        ));

        return response()->json([
            'success' => true,
            'data' => $traduccion
        ]);
    }
}
