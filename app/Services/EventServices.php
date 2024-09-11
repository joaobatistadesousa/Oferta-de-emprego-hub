<?php
namespace App\Services;

use App\Models\Event;

class EventServices
{
    public function getEventById($idevento)
    {
        return Event::where('idevento', $idevento)
            ->select('idevento', 'evento', 'data', 'hora', 'contato', 'valor', 'endereco')
            ->first(); // Retorna o primeiro resultado encontrado ou null
    }
}
