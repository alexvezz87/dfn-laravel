<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Contracts\View\View;

class BookingPublicController extends Controller
{
    /**
     * Mostra la schermata pubblica di riepilogo della prenotazione e biglietto d'ingresso.
     */
    public function show(string $code): View
    {
        $booking = Booking::with(['event', 'slot'])
            ->where('booking_code', $code)
            ->firstOrFail();

        return view('bookings.show', [
            'booking' => $booking,
            'event' => $booking->event,
            'slot' => $booking->slot,
        ]);
    }
}
