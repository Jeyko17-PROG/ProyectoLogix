<?php

namespace App\Modules\Activity\Controllers;

use App\Exports\ActividadExport;
use App\Http\Controllers\Controller;
use App\Models\Sesion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Excel;
use Illuminate\Support\Str;

class ActividadController extends Controller
{
    public function promptPin()
    {
        if ($this->hasAccess()) {
            return redirect()->route('actividad.index');
        }

        return view('modules.activity.pin');
    }

    public function verifyPin(Request $request)
    {
        $data = $request->validate([
            'pin' => ['required', 'digits:4'],
        ]);

        if ($data['pin'] !== config('spikia.activity_pin', '1234')) {
            return back()->withErrors(['pin' => 'Pin invalido.']);
        }

        session(['actividad_pin_verified' => true]);

        return redirect()->route('actividad.index');
    }

    public function index(Request $request)
    {
        abort_unless($this->hasAccess(), 403);

        $q = trim((string) $request->query('q', ''));
        $sesionesConEstadisticas = $this->buildSesionesConEstadisticas();

        if ($q !== '') {
            $sesionesConEstadisticas = $this->filterSesiones($sesionesConEstadisticas, $q);
        }

        return view('modules.activity.index', compact('sesionesConEstadisticas', 'q'));
    }

    public function exportarExcel(Request $request)
    {
        abort_unless($this->hasAccess(), 403);

        $q = trim((string) $request->query('q', ''));
        $data = $this->buildSesionesConEstadisticas();

        if ($q !== '') {
            $data = $this->filterSesiones($data, $q);
        }

        return Excel::download(new ActividadExport($data), 'log-actividad.xlsx');
    }

    private function hasAccess(): bool
    {
        return auth()->check()
            && (
                auth()->user()->email === 'luisgarciab193@gmail.com'
                || (bool) session('actividad_pin_verified', false)
            );
    }

    private function buildSesionesConEstadisticas()
    {
        return Sesion::withCount('transcripciones')
            ->with(['transcripciones' => fn ($query) => $query->latest()->limit(1)])
            ->where('user_id', auth()->id())
            ->latest()
            ->get()
            ->map(function (Sesion $sesion) {
                $inicio = $this->parseDateTime($sesion->fecha_inicio, $sesion->hora_inicio);
                $fin = $this->parseDateTime($sesion->fecha_inicio, $sesion->hora_fin);
                $duracionHoras = null;

                if ($inicio && $fin && $fin->greaterThan($inicio)) {
                    $duracionHoras = round($inicio->diffInMinutes($fin) / 60, 2);
                }

                return [
                    'sesion' => $sesion,
                    'titulo' => $sesion->titulo,
                    'slug' => $sesion->slug,
                    'fecha' => $sesion->fecha_inicio,
                    'hora_inicio' => $sesion->hora_inicio,
                    'hora_fin' => $sesion->hora_fin,
                    'presentador' => auth()->user()->name,
                    'transcripciones_count' => (int) $sesion->transcripciones_count,
                    'duracion_horas' => $duracionHoras,
                    'ultimo_texto' => $sesion->transcripciones->first()?->texto,
                ];
            });
    }

    private function filterSesiones($sesiones, string $q)
    {
        $needle = Str::lower($q);

        return collect($sesiones)->filter(function (array $sesion) use ($needle) {
            $texto = collect([
                $sesion['titulo'] ?? '',
                $sesion['slug'] ?? '',
                $sesion['presentador'] ?? '',
                $sesion['fecha'] ?? '',
                $sesion['hora_inicio'] ?? '',
                $sesion['hora_fin'] ?? '',
                $sesion['ultimo_texto'] ?? '',
            ])->implode(' ');

            return Str::contains(Str::lower($texto), $needle);
        })->values();
    }

    private function parseDateTime($date, $time): ?Carbon
    {
        if (! $date || ! $time) {
            return null;
        }

        try {
            return Carbon::parse(trim((string) $date . ' ' . (string) $time));
        } catch (\Throwable) {
            return null;
        }
    }
}