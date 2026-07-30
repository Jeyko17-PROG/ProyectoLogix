<?php

namespace App\Console\Commands;

use App\Models\Sesion;
use App\Models\SessionUsageEvent;
use Illuminate\Console\Command;

class SpikiaCleanupSessions extends Command
{
    protected $signature = 'spikia:sessions:cleanup {--user-id= : Limita la limpieza a un usuario}';
    protected $description = 'Elimina demos vencidas y sesiones vencidas fuera de la ventana de extension';

    public function handle(): int
    {
        $query = Sesion::query();

        if ($this->option('user-id')) {
            $query->where('user_id', (int) $this->option('user-id'));
        }

        $deleted = 0;

        $query->get()->each(function (Sesion $sesion) use (&$deleted) {
            if (! $sesion->session_expired_for_deletion) {
                return;
            }

            SessionUsageEvent::create([
                'user_id' => $sesion->user_id,
                'sesion_id' => $sesion->id,
                'slug' => $sesion->slug,
                'action' => $sesion->demo_expires_at ? 'demo_auto_deleted' : 'session_auto_deleted',
                'metadata' => [
                    'titulo' => $sesion->titulo,
                    'hora_fin' => $sesion->hora_fin,
                    'extra_time_minutes' => (int) ($sesion->extra_time_minutes ?? 0),
                    'extension_count' => (int) ($sesion->extension_count ?? 0),
                ],
                'occurred_at' => now(),
            ]);

            $sesion->delete();
            $deleted++;
        });

        $this->info("Sesiones eliminadas: {$deleted}");

        return self::SUCCESS;
    }
}
