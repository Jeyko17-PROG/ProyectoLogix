<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevaTraduccion implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $slug;
    public $texto;
    public $traduccion;
    public $idioma;

    public function __construct($slug, $texto, $traduccion, $idioma)
    {
        $this->slug = $slug;
        $this->texto = $texto;
        $this->traduccion = $traduccion;
        $this->idioma = $idioma;
    }

    public function broadcastOn()
    {
        return new Channel('transmision.' . $this->slug);
    }

    public function broadcastAs()
    {
        return 'nueva-traduccion';
    }
}
