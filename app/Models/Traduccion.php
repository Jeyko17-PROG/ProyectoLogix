<?php

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\Channel;

class TraduccionGenerada implements ShouldBroadcast
{
    public $slug;
    public $texto;
    public $traduccion;

    public function __construct($slug, $texto, $traduccion)
    {
        $this->slug = $slug;
        $this->texto = $texto;
        $this->traduccion = $traduccion;
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
