<?php

namespace App\Http\Controllers;

use App\Models\Sesion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Events\TraduccionEnviada; // <--- IMPORTANTE: Añade esta línea

class SesionController extends Controller
{
    public function index()
    {
        $sesiones = Sesion::all();
        return view('sesiones.index', compact('sesiones'));
    }

    public function store(Request $request)
    {
        $sesion = new Sesion();
        $sesion->titulo = $request->titulo;
        $sesion->presentador = $request->presentador;
        $sesion->cuenta = $request->cuenta;
        $sesion->fecha_inicio = $request->fecha_inicio;
        $sesion->hora_inicio = $request->hora_inicio;
        $sesion->zoom_link = $request->zoom_link;
        $sesion->idiomas = json_encode($request->idiomas ?? []); 
        $sesion->subtitulos = json_encode($request->subtitulos ?? []);
        $sesion->slug = Str::slug($request->titulo) . '-' . rand(100, 999);
        $sesion->save();

        return redirect()->route('sesiones');
    }

    public function update(Request $request, $id)
    {
        $sesion = Sesion::findOrFail($id);
        $sesion->titulo = $request->titulo;
        $sesion->presentador = $request->presentador;
        $sesion->cuenta = $request->cuenta;
        $sesion->fecha_inicio = $request->fecha_inicio;
        $sesion->hora_inicio = $request->hora_inicio;
        $sesion->zoom_link = $request->zoom_link;
        $sesion->idiomas = json_encode($request->idiomas ?? []);
        $sesion->subtitulos = json_encode($request->subtitulos ?? []);
        $sesion->save();

        return redirect()->route('sesiones');
    }

    public function destroy($id)
    {
        $sesion = Sesion::findOrFail($id);
        $sesion->delete();
        return redirect()->route('sesiones');
    }

    public function reunion($slug) 
    { 
        $sesion = Sesion::where('slug', $slug)->firstOrFail();
        return view('sesiones.reunion', compact('sesion')); 
    }

    public function master($slug) 
    { 
        $sesion = Sesion::where('slug', $slug)->firstOrFail();
        return view('sesiones.master', compact('sesion')); 
    }

    public function transmision($slug) 
    { 
        $sesion = Sesion::where('slug', $slug)->firstOrFail();
        return view('sesiones.transmision', compact('sesion')); 
    }

    /**
     * NUEVO MÉTODO: Recibe la traducción del Master y la dispara a Pusher
     */
    public function enviarTraduccion(Request $request)
    {
        $sesion_id = $request->input('sesion_id');
        $texto = $request->input('texto');
        $idioma = $request->input('idioma');

        // Disparamos el evento para que llegue a los móviles
        broadcast(new TraduccionEnviada($sesion_id, $texto, $idioma))->toOthers();

        return response()->json(['status' => 'Transmitido']);
    }
}