<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookingResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
    }

    public function test_can_render_booking_list_page(): void
    {
        $this->actingAs($this->admin)
            ->get(BookingResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_list_bookings_in_table(): void
    {
        $event = Event::factory()->create(['title' => 'Concerto Barocco']);
        $booking = Booking::factory()->create([
            'event_id' => $event->id,
            'customer_name' => 'Mario Rossi',
            'customer_email' => 'mario.rossi@example.com',
        ]);

        Livewire::actingAs($this->admin)
            ->test(BookingResource\Pages\ListBookings::class)
            ->assertCanSeeTableRecords([$booking])
            ->assertSee('Mario Rossi')
            ->assertSee('Concerto Barocco');
    }

    public function test_can_perform_quick_checkin_action(): void
    {
        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'checkin_at' => null,
            'checkin_status' => 'pending',
        ]);

        Livewire::actingAs($this->admin)
            ->test(BookingResource\Pages\ListBookings::class)
            ->callTableAction('checkin', $booking);

        $this->assertNotNull($booking->fresh()->checkin_at);
        $this->assertEquals('completed', $booking->fresh()->checkin_status);
    }

    public function test_can_undo_checkin_action(): void
    {
        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'checkin_at' => now(),
            'checkin_status' => 'completed',
        ]);

        Livewire::actingAs($this->admin)
            ->test(BookingResource\Pages\ListBookings::class)
            ->callTableAction('undo_checkin', $booking);

        $this->assertNull($booking->fresh()->checkin_at);
        $this->assertEquals('pending', $booking->fresh()->checkin_status);
    }

    public function test_can_approve_pending_fai_booking(): void
    {
        $booking = Booking::factory()->create([
            'status' => 'pending_approval',
        ]);

        Livewire::actingAs($this->admin)
            ->test(BookingResource\Pages\ListBookings::class)
            ->callTableAction('approve_fai', $booking);

        $this->assertEquals('confirmed', $booking->fresh()->status);
    }
}
