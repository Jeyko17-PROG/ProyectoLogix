<?php

namespace App\Modules\Transcriptions\Controllers;

use App\Events\TranscripcionCreada;
use App\Http\Controllers\Controller;
use App\Models\Sesion;
use App\Models\Transcripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TranscripcionController extends Controller
{
    public function index($slug)
    {
        $sesion = Sesion::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('modules.transcriptions.index', compact('sesion'));
    }

    public function listado(Request $request)
    {
        $modo = $request->query('modo');
        $q = trim((string) $request->query('q', ''));

        $transcripciones = Transcripcion::query()
            ->with('sesion')
            ->whereHas('sesion', fn ($query) => $query->where('user_id', auth()->id()))
            ->latest()
            ->get();

        $resumenes = $transcripciones
            ->groupBy('slug')
            ->map(function (Collection $grupo) {
                $sesion = $grupo->first()?->sesion;
                $idiomas = $grupo
                    ->groupBy('idioma')
                    ->map(function (Collection $items, $idioma) {
                        $itemsOrdenados = $items->sortBy('created_at')->values();
                        $ultimo = $itemsOrdenados->last();

                        return [
                            'idioma' => $idioma,
                            'modo' => $itemsOrdenados->first()?->modo ?? 'resumen',
                            'items' => $itemsOrdenados,
                            'texto' => $ultimo?->texto ?? '',
                            'updated_at' => $ultimo?->updated_at,
                        ];
                    })
                    ->values();

                return [
                    'sesion' => $sesion,
                    'slug' => $grupo->first()?->slug,
                    'transcripciones_count' => $grupo->count(),
                    'idiomas' => $idiomas,
                ];
            })
            ->values();

        if ($modo === 'resumen') {
            $resumenes = $resumenes->map(function (array $resumen) {
                $resumen['idiomas'] = $resumen['idiomas']
                    ->filter(fn ($idioma) => ($idioma['modo'] ?? 'resumen') === 'resumen')
                    ->values();

                return $resumen;
            })->filter(fn (array $resumen) => $resumen['idiomas']->isNotEmpty())->values();
        }

        if ($modo === 'detalle') {
            $resumenes = $resumenes->map(function (array $resumen) {
                $resumen['idiomas'] = $resumen['idiomas']
                    ->filter(fn ($idioma) => ($idioma['modo'] ?? 'resumen') === 'detalle')
                    ->values();

                return $resumen;
            })->filter(fn (array $resumen) => $resumen['idiomas']->isNotEmpty())->values();
        }

        if ($q !== '') {
            $needle = Str::lower($q);

            $resumenes = $resumenes->map(function (array $resumen) use ($needle) {
                $sesion = $resumen['sesion'];
                $slug = (string) ($resumen['slug'] ?? '');

                $matchesSession = Str::contains(Str::lower((string) ($sesion?->titulo ?? '')), $needle)
                    || Str::contains(Str::lower($slug), $needle)
                    || Str::contains(Str::lower((string) ($sesion?->slug ?? '')), $needle);

                $resumen['idiomas'] = $resumen['idiomas']->map(function (array $idioma) use ($needle) {
                    $items = $idioma['items']->filter(function ($item) use ($needle) {
                        return Str::contains(Str::lower((string) $item->texto), $needle)
                            || Str::contains(Str::lower((string) $item->idioma), $needle);
                    })->values();

                    $idioma['items'] = $items;
                    $idioma['texto'] = $items->last()?->texto ?? '';
                    $idioma['updated_at'] = $items->last()?->updated_at;

                    return $idioma;
                })->filter(fn (array $idioma) => $idioma['items']->isNotEmpty())->values();

                $resumen['matches_search'] = $matchesSession || $resumen['idiomas']->isNotEmpty();

                return $resumen;
            })->filter(fn (array $resumen) => ($resumen['matches_search'] ?? false))->values();
        }

        return view('modules.transcriptions.listado', compact('resumenes', 'modo', 'q'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'texto' => ['required', 'string'],
            'idioma' => ['required', 'string'],
            'slug' => ['required', 'string'],
            'sesion_id' => ['required', 'integer'],
        ]);

        $transcripcion = Transcripcion::create([
            'user_id' => auth()->id(),
            'sesion_id' => $request->sesion_id,
            'slug' => $request->slug,
            'texto' => $request->texto,
            'idioma' => $request->idioma,
            'audio_url' => $request->input('audio_url'),
            'modo' => $request->input('modo', 'resumen'),
        ]);

        broadcast(new TranscripcionCreada($transcripcion, $request->slug))->toOthers();

        return response()->json([
            'success' => true,
            'data' => $transcripcion,
        ]);
    }

    public function grabarSesion(Request $request)
    {
        $request->validate([
            'sesion_id' => ['required', 'integer'],
            'slug' => ['required', 'string'],
            'grabacion' => ['required', 'file'],
        ]);

        $sesion = Sesion::where('id', $request->sesion_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $path = $request->file('grabacion')->storeAs(
            'media/transmisiones/' . Str::slug($request->slug),
            $request->file('grabacion')->getClientOriginalName(),
            'public'
        );

        $publicUrl = Storage::disk('public')->url($path);
        $url = preg_replace('#^/storage#', '', parse_url($publicUrl, PHP_URL_PATH) ?: $publicUrl);

        $sesion->grabacion_url = $url;
        $sesion->save();

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => $url,
        ]);
    }

    public function descargar($slug, $tipo, $idioma = null)
    {
        $transcripciones = Transcripcion::where('slug', $slug)
            ->whereHas('sesion', fn ($query) => $query->where('user_id', auth()->id()))
            ->when($idioma, fn ($query) => $query->where('idioma', $idioma))
            ->latest()
            ->get();

        if ($tipo === 'texto') {
            $contenido = $transcripciones->pluck('texto')->filter()->implode("\n\n");

            return response($contenido, 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $slug . '.txt"',
            ]);
        }

        $audioUrl = $transcripciones->first()?->audio_url;

        if (! $audioUrl) {
            $sesion = Sesion::where('slug', $slug)
                ->where('user_id', auth()->id())
                ->first();

            $audioUrl = $sesion?->grabacion_url;
        }

        if (! $audioUrl) {
            abort(404, 'No hay audio disponible.');
        }

        return redirect($audioUrl);
    }
}