<?php

namespace App\Http\Controllers;

use App\Actions\CreateBookingAction;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Event;
use App\Models\FaiMember;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventPublicController extends Controller
{
    /**
     * Mostra la scheda pubblica dell'evento (anteprima o vista partecipante).
     */
    public function show(Event $event): View
    {
        $event->load(['slots' => function ($query): void {
            $query->where('status', 'active')
                ->where('is_locked', false)
                ->orderBy('slot_date')
                ->orderBy('start_time');
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

    /**
     * Elabora l'invio della prenotazione da parte del visitatore.
     */
    public function book(Event $event, StoreBookingRequest $request, CreateBookingAction $action): RedirectResponse
    {
        $booking = $action->execute($event, $request->validated());

        return redirect()->route('booking.show', ['code' => $booking->booking_code])
            ->with('success', 'Prenotazione confermata con successo!');
    }

    /**
     * Verifica asincrona in tempo reale della validità di un numero di tessera Socio FAI.
     */
    public function checkFaiCard(Request $request): JsonResponse
    {
        $cardNumber = trim((string) $request->input('card_number'));

        if (empty($cardNumber)) {
            return response()->json([
                'valid' => false,
                'message' => 'Inserisci un numero di tessera.',
            ]);
        }

        /** @var FaiMember|null $member */
        $member = FaiMember::where('card_number', $cardNumber)->first();

        if (! $member) {
            return response()->json([
                'valid' => false,
                'message' => 'Tessera non censita: la quota ridotta potrà essere verificata dal personale all\'ingresso.',
            ]);
        }

        if ($member->expiry_date && Carbon::parse($member->expiry_date)->isPast()) {
            return response()->json([
                'valid' => false,
                'message' => 'Tessera FAI scaduta il '.Carbon::parse($member->expiry_date)->format('d/m/Y').': sarà richiesta regolarizzazione.',
            ]);
        }

        if ($member->status !== 'active') {
            return response()->json([
                'valid' => false,
                'message' => 'Tessera non attiva.',
            ]);
        }

        $memberName = trim("{$member->first_name} {$member->last_name}");

        return response()->json([
            'valid' => true,
            'message' => "Tessera Socio FAI valida! Intestata a: {$memberName}",
            'member_name' => $memberName,
        ]);
    }
}
