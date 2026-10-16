<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\EventResource;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EventResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@dfnprenotazioni.it',
        ]);
    }

    public function test_can_render_event_list_page(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(EventResource::getUrl('index'));

        $response->assertSuccessful();
    }

    public function test_can_list_upcoming_and_past_events(): void
    {
        $upcomingEvent = Event::factory()->create([
            'title' => 'Concerto di Primavera',
            'event_date_start' => now()->addDays(10)->toDateString(),
            'event_date_end' => now()->addDays(12)->toDateString(),
            'status' => 'published',
        ]);

        $pastEvent = Event::factory()->create([
            'title' => 'Visita Storica Autunnale',
            'event_date_start' => now()->subDays(20)->toDateString(),
            'event_date_end' => now()->subDays(18)->toDateString(),
            'status' => 'archived',
            'total_capacity' => 0,
        ]);

        Slot::factory()->create([
            'event_id' => $pastEvent->id,
            'max_capacity' => 100,
        ]);

        Livewire::actingAs($this->admin)
            ->test(EventResource\Pages\ListEvents::class)
            ->assertCanSeeTableRecords([$upcomingEvent])
            ->set('activeTab', 'past')
            ->assertCanSeeTableRecords([$pastEvent])
            ->assertCanNotSeeTableRecords([$upcomingEvent]);
    }

    public function test_can_render_event_create_page(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(EventResource::getUrl('create'));

        $response->assertSuccessful();
    }

    public function test_can_render_manage_event_bookings_page(): void
    {
        $this->actingAs($this->admin);

        $event = Event::factory()->create([
            'title' => 'United Gospel 2026',
        ]);

        $booking = Booking::factory()->create([
            'event_id' => $event->id,
            'customer_name' => 'Simona Greco',
            'status' => 'confirmed',
        ]);

        $response = $this->get(EventResource::getUrl('bookings', ['record' => $event]));

        $response->assertSuccessful();

        Livewire::actingAs($this->admin)
            ->test(EventResource\Pages\ManageEventBookings::class, [
                'record' => $event->getRouteKey(),
            ])
            ->assertCanSeeTableRecords([$booking]);
    }
}
