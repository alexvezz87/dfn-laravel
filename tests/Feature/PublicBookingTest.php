<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\FaiMember;
use App\Models\Slot;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_public_event_page(): void
    {
        $event = Event::create([
            'title' => 'Concerto di Primavera FAI',
            'access_type' => 'free_flow',
            'price_standard' => 15.00,
            'price_fai' => 10.00,
            'event_date_start' => Carbon::tomorrow(),
            'event_date_end' => Carbon::tomorrow(),
            'is_active' => true,
        ]);

        $response = $this->get(route('event.preview', $event));

        $response->assertStatus(200);
        $response->assertSee('Concerto di Primavera FAI');
        $response->assertSee('Seleziona Partecipanti');
    }

    public function test_can_create_booking_for_free_flow_event(): void
    {
        $event = Event::create([
            'title' => 'Visita Villa Panza',
            'access_type' => 'free_flow',
            'price_standard' => 12.00,
            'price_fai' => 8.00,
            'event_date_start' => Carbon::tomorrow(),
            'is_active' => true,
            'total_capacity' => 100,
        ]);

        $payload = [
            'persons_standard' => 2,
            'persons_fai' => 0,
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'email' => 'mario.rossi@example.com',
            'phone' => '3331234567',
            'notes' => 'Arrivo previsto alle 11:00',
            'payment_method' => 'in_loco',
        ];

        $response = $this->post(route('event.book', $event), $payload);

        $this->assertDatabaseHas('bookings', [
            'event_id' => $event->id,
            'customer_name' => 'Mario Rossi',
            'customer_email' => 'mario.rossi@example.com',
            'total_persons' => 2,
            'persons_standard' => 2,
            'persons_fai' => 0,
            'amount_due' => 24.00,
            'status' => 'confirmed',
        ]);

        $booking = Booking::first();
        $this->assertNotNull($booking->booking_code);
        $response->assertRedirect(route('booking.show', ['code' => $booking->booking_code]));
    }

    public function test_can_create_booking_for_time_slots_event_and_increments_booked_count(): void
    {
        $event = Event::create([
            'title' => 'Giornate FAI d\'Autunno',
            'access_type' => 'time_slots',
            'price_standard' => 10.00,
            'price_fai' => 5.00,
            'is_active' => true,
        ]);

        $slot = Slot::create([
            'event_id' => $event->id,
            'slot_date' => Carbon::tomorrow(),
            'start_time' => '10:00:00',
            'end_time' => '10:45:00',
            'max_capacity' => 15,
            'booked_count' => 0,
            'status' => 'active',
            'is_locked' => false,
        ]);

        $payload = [
            'slot_id' => $slot->id,
            'persons_standard' => 3,
            'persons_fai' => 0,
            'first_name' => 'Luigi',
            'last_name' => 'Verdi',
            'email' => 'luigi.verdi@example.com',
            'phone' => '3339876543',
            'payment_method' => 'in_loco',
        ];

        $response = $this->post(route('event.book', $event), $payload);

        $booking = Booking::where('customer_email', 'luigi.verdi@example.com')->first();
        $this->assertNotNull($booking);
        $this->assertEquals($slot->id, $booking->slot_id);
        $this->assertEquals(3, $booking->total_persons);

        $slot->refresh();
        $this->assertEquals(3, $slot->booked_count);
        $this->assertEquals(12, $slot->availableCapacity());

        $response->assertRedirect(route('booking.show', ['code' => $booking->booking_code]));
    }

    public function test_cannot_book_slot_when_capacity_exceeded(): void
    {
        $event = Event::create([
            'title' => 'Tour Limitato',
            'access_type' => 'time_slots',
            'price_standard' => 10.00,
            'is_active' => true,
        ]);

        $slot = Slot::create([
            'event_id' => $event->id,
            'slot_date' => Carbon::tomorrow(),
            'start_time' => '14:00:00',
            'end_time' => '14:30:00',
            'max_capacity' => 2,
            'booked_count' => 0,
            'status' => 'active',
            'is_locked' => false,
        ]);

        $payload = [
            'slot_id' => $slot->id,
            'persons_standard' => 4, // Richiede 4 posti su 2 disponibili
            'persons_fai' => 0,
            'first_name' => 'Anna',
            'last_name' => 'Bianchi',
            'email' => 'anna.bianchi@example.com',
            'phone' => '3330001122',
        ];

        $response = $this->post(route('event.book', $event), $payload);

        $response->assertSessionHasErrors('slot_id');
        $this->assertDatabaseMissing('bookings', ['customer_email' => 'anna.bianchi@example.com']);
        $this->assertEquals(0, $slot->fresh()->booked_count);
    }

    public function test_booking_with_valid_fai_card_sets_status_confirmed(): void
    {
        $event = Event::create([
            'title' => 'Convegno FAI',
            'access_type' => 'free_flow',
            'price_standard' => 20.00,
            'price_fai' => 10.00,
            'is_active' => true,
        ]);

        FaiMember::create([
            'card_number' => 'FAI-998877',
            'first_name' => 'Giulia',
            'last_name' => 'Neri',
            'status' => 'active',
            'expiry_date' => Carbon::now()->addMonths(6),
        ]);

        $payload = [
            'persons_standard' => 1,
            'persons_fai' => 1,
            'fai_cards' => ['FAI-998877'],
            'first_name' => 'Giulia',
            'last_name' => 'Neri',
            'email' => 'giulia.neri@example.com',
            'phone' => '3335556677',
            'payment_method' => 'in_loco',
        ];

        $response = $this->post(route('event.book', $event), $payload);

        $booking = Booking::where('customer_email', 'giulia.neri@example.com')->first();
        $this->assertNotNull($booking);
        $this->assertEquals('confirmed', $booking->status);
        $this->assertEquals(30.00, (float) $booking->amount_due);
        $this->assertTrue($booking->fai_cards[0]['is_valid']);
        $this->assertEquals('FAI-998877', $booking->fai_cards[0]['card_number']);

        $response->assertRedirect(route('booking.show', ['code' => $booking->booking_code]));
    }

    public function test_booking_with_unregistered_fai_card_sets_status_pending_fai(): void
    {
        $event = Event::create([
            'title' => 'Concerto Notturno FAI',
            'access_type' => 'free_flow',
            'price_standard' => 15.00,
            'price_fai' => 5.00,
            'is_active' => true,
        ]);

        $payload = [
            'persons_standard' => 0,
            'persons_fai' => 1,
            'fai_cards' => ['NON-EXISTENT-CARD'],
            'first_name' => 'Marco',
            'last_name' => 'Gialli',
            'email' => 'marco.gialli@example.com',
            'phone' => '3338889900',
            'payment_method' => 'in_loco',
        ];

        $response = $this->post(route('event.book', $event), $payload);

        $booking = Booking::where('customer_email', 'marco.gialli@example.com')->first();
        $this->assertNotNull($booking);
        $this->assertEquals('pending_fai', $booking->status);
        $this->assertFalse($booking->fai_cards[0]['is_valid']);

        $response->assertRedirect(route('booking.show', ['code' => $booking->booking_code]));
    }

    public function test_can_view_public_booking_ticket_page(): void
    {
        $event = Event::create([
            'title' => 'Concerto Biglietto',
            'access_type' => 'free_flow',
            'price_standard' => 10.00,
            'is_active' => true,
        ]);

        $booking = Booking::create([
            'event_id' => $event->id,
            'customer_name' => 'Paolo Verdi',
            'customer_email' => 'paolo.verdi@example.com',
            'total_persons' => 1,
            'persons_standard' => 1,
            'persons_fai' => 0,
            'amount_due' => 10.00,
            'amount_paid' => 0.00,
            'status' => 'confirmed',
            'booking_code' => 'DFN-2026-TEST01',
        ]);

        $response = $this->get(route('booking.show', ['code' => 'DFN-2026-TEST01']));

        $response->assertStatus(200);
        $response->assertSee('DFN-2026-TEST01');
        $response->assertSee('Paolo Verdi');
        $response->assertSee('Concerto Biglietto');
    }

    public function test_check_fai_card_endpoint(): void
    {
        FaiMember::create([
            'card_number' => '12345678',
            'first_name' => 'Sara',
            'last_name' => 'Bruni',
            'status' => 'active',
            'expiry_date' => Carbon::now()->addYear(),
        ]);

        // Card valida
        $response = $this->postJson(route('fai.check-card'), [
            'card_number' => '12345678',
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'member_name' => 'Sara Bruni',
        ]);

        // Card non esistente
        $responseNotFound = $this->postJson(route('fai.check-card'), [
            'card_number' => '00000000',
        ]);
        $responseNotFound->assertStatus(200);
        $responseNotFound->assertJson([
            'valid' => false,
        ]);
    }
}
