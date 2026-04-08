<?php

namespace App\Modules\Sessions\Controllers;

use App\Events\TranscripcionCreada;
use App\Events\TraduccionEnviada;
use App\Http\Controllers\Controller;
use App\Models\Glosario;
use App\Models\Sesion;
use App\Models\Transcripcion;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SesionController extends Controller
{
    public function index()
    {
        $sesiones = Sesion::with(['glosario', 'user'])
            ->withCount('transcripciones')
            ->where('user_id', auth()->id())
            ->get();

        $glosarios = Glosario::where('user_id', auth()->id())->get();

        $resumenContenido = [
            'sesiones' => Sesion::where('user_id', auth()->id())->count(),
            'glosarios' => Glosario::where('user_id', auth()->id())->count(),
            'transcripciones' => Transcripcion::where('user_id', auth()->id())->count(),
            'videos' => Video::where('user_id', auth()->id())->count(),
        ];

        return view('modules.sessions.index', compact('sesiones', 'glosarios', 'resumenContenido'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'fecha_inicio' => ['nullable', 'string', 'max:255'],
            'hora_inicio' => ['nullable', 'string', 'max:255'],
            'hora_fin' => ['nullable', 'string', 'max:255'],
            'glosario_id' => [
                'nullable',
                'integer',
                Rule::exists('glosarios', 'id')->where(fn ($query) => $query->where('user_id', auth()->id())),
            ],
            'idiomas' => ['nullable', 'array'],
        ]);

        $sesion = new Sesion();
        $sesion->user_id = auth()->id();
        $sesion->titulo = $data['titulo'];
        $sesion->fecha_inicio = $data['fecha_inicio'] ?? null;
        $sesion->hora_inicio = $data['hora_inicio'] ?? null;
        $sesion->hora_fin = $data['hora_fin'] ?? null;
        $sesion->glosario_id = $data['glosario_id'] ?? null;
        $sesion->idiomas = $data['idiomas'] ?? [];
        $sesion->slug = Str::slug($data['titulo']) . '-' . rand(100, 999);
        $sesion->save();

        return redirect()->route('sesiones.index');
    }

    public function edit($id)
    {
        $sesion = Sesion::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $glosarios = Glosario::where('user_id', auth()->id())->get();

        return view('modules.sessions.edit', compact('sesion', 'glosarios'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'fecha_inicio' => ['nullable', 'string', 'max:255'],
            'hora_inicio' => ['nullable', 'string', 'max:255'],
            'hora_fin' => ['nullable', 'string', 'max:255'],
            'glosario_id' => [
                'nullable',
                'integer',
                Rule::exists('glosarios', 'id')->where(fn ($query) => $query->where('user_id', auth()->id())),
            ],
            'idiomas' => ['nullable', 'array'],
        ]);

        $sesion = Sesion::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $sesion->titulo = $data['titulo'];
        $sesion->fecha_inicio = $data['fecha_inicio'] ?? null;
        $sesion->hora_inicio = $data['hora_inicio'] ?? null;
        $sesion->hora_fin = $data['hora_fin'] ?? null;
        $sesion->glosario_id = $data['glosario_id'] ?? null;
        $sesion->idiomas = $data['idiomas'] ?? [];
        $sesion->save();

        return redirect()->route('sesiones.index')->with('success', 'Configuracion actualizada');
    }

    public function destroy($id)
    {
        $sesion = Sesion::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $sesion->delete();

        return redirect()->route('sesiones.index');
    }

    public function reunion($slug)
    {
        $sesion = Sesion::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('modules.sessions.reunion', compact('sesion'));
    }

    public function master($slug)
    {
        $sesion = Sesion::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('modules.sessions.master', compact('sesion'));
    }

    public function transmision($slug)
    {
        $sesion = Sesion::where('slug', $slug)->firstOrFail();

        return response()
            ->view('modules.sessions.transmision', compact('sesion'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function movil($slug)
    {
        $sesion = Sesion::where('slug', $slug)->firstOrFail();

        return response()
            ->view('modules.sessions.transmision', compact('sesion'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
    public function activarIdioma(Request $request, $id)
    {
        $sesion = Sesion::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $sesion->idioma_activo = $request->idioma;
        $sesion->save();

        return response()->json(['status' => 'success']);
    }

    public function enviarTraduccion(Request $request)
    {
        $sesion_id = $request->input('sesion_id');
        $texto = $request->input('texto');
        $idioma = $request->input('idioma');
        $sesion = Sesion::find($sesion_id);

        if ($sesion) {
            broadcast(new TraduccionEnviada($sesion->slug, $texto, $idioma))->toOthers();
        }

        return response()->json(['status' => 'Transmitido']);
    }

    public function publicarMensaje(Request $request, string $slug)
    {
        $data = $request->validate([
            'id' => ['nullable', 'string', 'max:80'],
            'texto' => ['required', 'string'],
            'idioma' => ['required', 'string', 'max:10'],
            'variante' => ['nullable', 'string', 'max:10'],
            'genero' => ['nullable', 'string', 'max:10'],
            'tipo' => ['nullable', 'string', 'max:20'],
        ]);

        $sesion = Sesion::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $cacheKey = $this->relayCacheKey($slug);
        $messages = Cache::get($cacheKey, []);
        $messageId = ! empty($data['id']) ? (string) $data['id'] : (string) Str::uuid();

        $messages[] = [
            'id' => $messageId,
            'texto' => $data['texto'],
            'idioma' => $data['idioma'],
            'variante' => $data['variante'] ?? null,
            'genero' => $data['genero'] ?? null,
            'tipo' => $data['tipo'] ?? 'texto',
            'published_at' => now()->timestamp,
            'available_at' => now()->addSeconds(3)->timestamp,
        ];

        $messages = array_slice($messages, -200);
        Cache::put($cacheKey, $messages, now()->addHours(12));

        try {
            $transcripcion = new Transcripcion([
                'sesion_id' => $sesion->id,
                'slug' => $slug,
                'texto' => $data['texto'],
                'idioma' => $data['idioma'],
                'audio_url' => null,
                'modo' => $data['tipo'] ?? 'texto',
            ]);
            $transcripcion->id = $messageId;

            event(new TranscripcionCreada($transcripcion, $slug));
        } catch (\Throwable $e) {
            \Log::error('No se pudo emitir la transcripcion en vivo: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'queued' => true,
            'available_in_seconds' => 3,
        ]);
    }

    public function feed(string $slug)
    {
        $messages = Cache::get($this->relayCacheKey($slug), []);
        $now = now()->timestamp;

        $available = array_values(array_filter($messages, function (array $message) use ($now) {
            return (int) ($message['available_at'] ?? 0) <= $now;
        }));

        $pending = array_values(array_filter($messages, function (array $message) use ($now) {
            return (int) ($message['available_at'] ?? 0) > $now;
        }));

        $nextAvailableIn = null;

        if (! empty($pending)) {
            $nextAvailableIn = max(0, (int) ($pending[0]['available_at'] ?? $now) - $now);
        }

        return response()->json([
            'success' => true,
            'messages' => $available,
            'pending_count' => count($pending),
            'next_available_in_seconds' => $nextAvailableIn,
        ]);
    }

    private function relayCacheKey(string $slug): string
    {
        return 'spikia:relay:' . Str::slug($slug);
    }
}








