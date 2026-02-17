<?php

namespace App\Events;

use App\Models\Transcripcion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TranscripcionCreada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transcripcion;
    public $slug;

    public function __construct(Transcripcion $transcripcion, $slug)
    {
        $this->transcripcion = $transcripcion;
        $this->slug = $slug;
    }

    public function broadcastOn()
    {
        return new Channel('transmision.' . $this->slug);
    }

    public function broadcastWith()
    {
        return [
            'texto' => $this->transcripcion->texto,
            'idioma' => $this->transcripcion->idioma,
        ];
    }
}
