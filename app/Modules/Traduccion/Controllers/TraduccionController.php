<?php

namespace App\Modules\Traduccion\Controllers;

use App\Events\NuevaTraduccion;
use App\Http\Controllers\Controller;
use App\Models\Sesion;
use App\Models\Traduccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TraduccionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'sesion_id' => ['required', 'integer'],
            'texto' => ['required', 'string'],
            'idioma' => ['required', 'string', 'max:10'],
        ]);

        $sesion = Sesion::find($data['sesion_id']);

        if (! $sesion) {
            return response()->json(['error' => 'Sesion no encontrada'], 404);
        }

        $textoTraducido = $this->translateText($data['texto'], $data['idioma']);

        $traduccion = Traduccion::create([
            'sesion_id' => $sesion->id,
            'texto_original' => $data['texto'],
            'texto_traducido' => $textoTraducido,
            'idioma' => $data['idioma'],
        ]);

        try {
            event(new NuevaTraduccion($sesion->slug, $traduccion->id, $textoTraducido, $data['idioma']));
        } catch (\Throwable $e) {
            \Log::error('Error en evento: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'traduccion' => $textoTraducido,
        ]);
    }

    private function translateText(string $text, string $targetLanguage): string
    {
        $deeplKey = (string) config('services.deepl.key', '');

        if ($deeplKey !== '') {
            try {
                $response = Http::asForm()
                    ->timeout(20)
                    ->post($this->deeplEndpoint() . '/v2/translate', [
                        'auth_key' => $deeplKey,
                        'text' => $text,
                        'target_lang' => $this->normalizeDeepLTarget($targetLanguage),
                    ]);

                if ($response->ok()) {
                    $translated = data_get($response->json(), 'translations.0.text');

                    if (is_string($translated) && trim($translated) !== '') {
                        return $translated;
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('DeepL no disponible, usando fallback local: ' . $e->getMessage());
            }
        }

        try {
            $translator = new GoogleTranslate($this->normalizeGoogleTarget($targetLanguage));
            $translator->setSource();
            return $translator->translate($text);
        } catch (\Throwable $e) {
            \Log::warning('Fallback de traduccion fallo: ' . $e->getMessage());
        }

        return $text;
    }

    private function deeplEndpoint(): string
    {
        $endpoint = (string) config('services.deepl.endpoint', '');

        if ($endpoint !== '') {
            return rtrim($endpoint, '/');
        }

        $key = (string) config('services.deepl.key', '');

        return str_ends_with($key, ':fx')
            ? 'https://api-free.deepl.com'
            : 'https://api.deepl.com';
    }

    private function normalizeDeepLTarget(string $targetLanguage): string
    {
        $base = strtolower(explode('-', $targetLanguage)[0]);

        return match ($base) {
            'pt' => 'PT-BR',
            'en' => 'EN-US',
            'es' => 'ES',
            'it' => 'IT',
            'fr' => 'FR',
            default => strtoupper($base),
        };
    }

    private function normalizeGoogleTarget(string $targetLanguage): string
    {
        return strtolower(explode('-', $targetLanguage)[0]);
    }
}
