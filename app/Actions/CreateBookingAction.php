<?php

namespace App\Actions;

use App\Models\Booking;
use App\Models\Event;
use App\Models\FaiMember;
use App\Models\Slot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateBookingAction
{
    /**
     * Esegue la creazione della prenotazione in transazione atomica con lock di capienza.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(Event $event, array $data): Booking
    {
        $personsStandard = max(0, (int) ($data['persons_standard'] ?? 0));
        $personsFai = max(0, (int) ($data['persons_fai'] ?? 0));
        $totalPersons = $personsStandard + $personsFai;

        if ($totalPersons <= 0) {
            throw ValidationException::withMessages([
                'persons_standard' => 'È necessario selezionare almeno un partecipante.',
            ]);
        }

        return DB::transaction(function () use ($event, $data, $personsStandard, $personsFai, $totalPersons): Booking {
            $slot = null;

            // 1. Gestione Capienza Slot o Flusso Libero
            if ($event->access_type === 'time_slots') {
                if (empty($data['slot_id'])) {
                    throw ValidationException::withMessages([
                        'slot_id' => 'È obbligatorio selezionare una fascia oraria per questo evento.',
                    ]);
                }

                /** @var Slot $slot */
                $slot = Slot::where('id', $data['slot_id'])
                    ->where('event_id', $event->id)
                    ->lockForUpdate()
                    ->first();

                if (! $slot) {
                    throw ValidationException::withMessages([
                        'slot_id' => 'La fascia oraria selezionata non è valida per questo evento.',
                    ]);
                }

                if ($slot->status !== 'active' || $slot->is_locked) {
                    throw ValidationException::withMessages([
                        'slot_id' => 'Il turno selezionato è momentaneamente bloccato o non attivo.',
                    ]);
                }

                $available = $slot->availableCapacity();
                if ($available < $totalPersons) {
                    throw ValidationException::withMessages([
                        'slot_id' => "I posti disponibili per questo turno non sono sufficienti (disponibili: {$available}, richiesti: {$totalPersons}).",
                    ]);
                }
            } else {
                // Flusso Libero: verifica capienza massima se configurata
                if ($event->total_capacity > 0) {
                    $bookedSum = (int) $event->bookings()
                        ->whereIn('status', ['confirmed', 'pending_fai', 'pending_payment'])
                        ->sum('total_persons');

                    if (($bookedSum + $totalPersons) > $event->total_capacity) {
                        $freeSeats = max(0, $event->total_capacity - $bookedSum);
                        throw ValidationException::withMessages([
                            'persons_standard' => "Spiacenti, la capienza massima dell'evento è esaurita (posti rimasti: {$freeSeats}).",
                        ]);
                    }
                }
            }

            // 2. Verifica Tessere Soci FAI
            $processedFaiCards = [];
            $hasUnverifiedFai = false;

            if ($personsFai > 0) {
                $rawCards = (array) ($data['fai_cards'] ?? []);

                for ($i = 0; $i < $personsFai; $i++) {
                    $cardNumber = isset($rawCards[$i]) ? trim((string) $rawCards[$i]) : '';

                    if (empty($cardNumber)) {
                        $processedFaiCards[] = [
                            'index' => $i + 1,
                            'card_number' => null,
                            'is_valid' => false,
                            'reason' => 'Numero tessera non fornito',
                        ];
                        $hasUnverifiedFai = true;

                        continue;
                    }

                    /** @var FaiMember|null $member */
                    $member = FaiMember::where('card_number', $cardNumber)->first();

                    if (! $member) {
                        $processedFaiCards[] = [
                            'index' => $i + 1,
                            'card_number' => $cardNumber,
                            'is_valid' => false,
                            'reason' => 'Tessera non censita a sistema',
                        ];
                        $hasUnverifiedFai = true;
                    } elseif ($member->expiry_date && Carbon::parse($member->expiry_date)->isPast()) {
                        $processedFaiCards[] = [
                            'index' => $i + 1,
                            'card_number' => $cardNumber,
                            'member_id' => $member->id,
                            'member_name' => trim("{$member->first_name} {$member->last_name}"),
                            'is_valid' => false,
                            'reason' => 'Tessera FAI scaduta il '.Carbon::parse($member->expiry_date)->format('d/m/Y'),
                        ];
                        $hasUnverifiedFai = true;
                    } elseif ($member->status !== 'active') {
                        $processedFaiCards[] = [
                            'index' => $i + 1,
                            'card_number' => $cardNumber,
                            'member_id' => $member->id,
                            'is_valid' => false,
                            'reason' => 'Tessera non attiva a sistema',
                        ];
                        $hasUnverifiedFai = true;
                    } else {
                        $processedFaiCards[] = [
                            'index' => $i + 1,
                            'card_number' => $cardNumber,
                            'member_id' => $member->id,
                            'member_name' => trim("{$member->first_name} {$member->last_name}"),
                            'is_valid' => true,
                            'reason' => 'Tessera valida',
                        ];
                    }
                }
            }

            // 3. Calcolo Importi
            $priceStandard = (float) $event->price_standard;
            $priceFai = (float) ($event->price_fai ?? $event->price_standard);
            $amountDue = ($personsStandard * $priceStandard) + ($personsFai * $priceFai);

            // 4. Stato Iniziale Prenotazione
            // Se vi sono tessere FAI non verificate o mancanti, stato 'pending_fai' per verifica al banchetto
            $status = $hasUnverifiedFai ? 'pending_fai' : 'confirmed';

            $customerName = trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''));
            if (empty($customerName) && ! empty($data['customer_name'])) {
                $customerName = trim((string) $data['customer_name']);
            }

            // 5. Creazione Record Prenotazione
            /** @var Booking $booking */
            $booking = Booking::create([
                'event_id' => $event->id,
                'slot_id' => $slot?->id,
                'customer_name' => $customerName,
                'customer_email' => $data['email'] ?? null,
                'customer_phone' => $data['phone'] ?? null,
                'total_persons' => $totalPersons,
                'persons_standard' => $personsStandard,
                'persons_fai' => $personsFai,
                'fai_cards' => $processedFaiCards,
                'status' => $status,
                'checkin_status' => 'pending',
                'payment_method' => $data['payment_method'] ?? 'in_loco',
                'amount_due' => $amountDue,
                'amount_paid' => 0.00,
                'notes' => $data['notes'] ?? null,
                'qr_code_hash' => Str::random(40),
            ]);

            // 6. Aggiornamento Contatore Slot
            if ($slot) {
                $slot->increment('booked_count', $totalPersons);
            }

            return $booking;
        });
    }
}
