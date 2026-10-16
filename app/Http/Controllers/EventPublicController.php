<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Contracts\View\View;

class EventPublicController extends Controller
{
    /**
     * Mostra la scheda pubblica dell'evento (anteprima o vista partecipante).
     */
    public function show(Event $event): View
    {
        $event->load(['slots' => function ($query): void {
            $query->where('status', 'active')->orderBy('slot_date')->orderBy('start_time');
        }]);

        // Determina il layout effettivo:
        // Se 'auto', usa layout2 se non ci sono immagini nella galleria, altrimenti layout1
        $effectiveLayout = $event->detail_layout;
        if ($effectiveLayout === 'auto' || empty($effectiveLayout)) {
            $effectiveLayout = ! empty($event->gallery_images) && count($event->gallery_images) > 0 ? 'layout1' : 'layout2';
        }

        return view('events.show', [
            'event' => $event,
            'effectiveLayout' => $effectiveLayout,
        ]);
    }
}
