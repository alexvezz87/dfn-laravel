<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\EventResource;
use App\Filament\Resources\EventResource\Pages\CreateEvent;
use App\Models\Booking;
use App\Models\Event;
use App\Models\MediaItem;
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

    public function test_can_render_event_edit_page(): void
    {
        $this->actingAs($this->admin);

        $event = Event::factory()->create([
            'title' => 'Visita Guidata al Borgo',
            'detail_layout' => 'layout2',
            'price_standard' => 15.00,
            'price_fai' => 10.00,
            'show_reviews_frontend' => true,
        ]);

        $response = $this->get(EventResource::getUrl('edit', ['record' => $event]));

        $response->assertSuccessful();

        Livewire::actingAs($this->admin)
            ->test(EventResource\Pages\EditEvent::class, [
                'record' => $event->getRouteKey(),
            ])
            ->assertActionExists('preview')
            ->assertActionExists('duplicate')
            ->assertActionExists('back')
            ->assertActionExists('bookings')
            ->assertFormFieldDoesNotExist('product_id');
    }

    public function test_can_render_public_event_preview_page(): void
    {
        $event = Event::factory()->create([
            'title' => 'United Gospel 2026 - Cento Voci un solo Cuore',
            'location' => 'Basilica di San Gaudenzio - Via S. Gaudenzio, 22',
            'city' => 'Novara',
            'price_standard' => 15.00,
            'price_fai' => 10.00,
            'detail_layout' => 'layout2',
        ]);

        $response = $this->get(route('event.preview', ['event' => $event->id]));

        $response->assertSuccessful();
        $response->assertSee('United Gospel 2026 - Cento Voci un solo Cuore');
        $response->assertSee('Basilica di San Gaudenzio');
        $response->assertSee('15.00');
        $response->assertSee('10.00');
        $response->assertSee('Modalità Anteprima Scheda Evento');
    }

    public function test_can_duplicate_event(): void
    {
        $event = Event::factory()->create([
            'title' => 'Concerto Candle Vibes',
            'status' => 'published',
            'price_standard' => 20.00,
        ]);

        $slot = Slot::factory()->create([
            'event_id' => $event->id,
            'max_capacity' => 50,
            'booked_count' => 10,
        ]);

        Livewire::actingAs($this->admin)
            ->test(EventResource\Pages\EditEvent::class, [
                'record' => $event->getRouteKey(),
            ])
            ->callAction('duplicate');

        $this->assertDatabaseHas('events', [
            'title' => '(Copia) Concerto Candle Vibes',
            'status' => 'draft',
            'price_standard' => 20.00,
        ]);

        $duplicatedEvent = Event::where('title', '(Copia) Concerto Candle Vibes')->first();
        $this->assertNotNull($duplicatedEvent);

        $this->assertDatabaseHas('slots', [
            'event_id' => $duplicatedEvent->id,
            'max_capacity' => 50,
            'booked_count' => 0,
        ]);
    }

    public function test_can_select_featured_image_from_media_library(): void
    {
        $this->actingAs($this->admin);

        $media = MediaItem::create([
            'disk' => 'public',
            'file_path' => 'uploads/locandina-archivio.webp',
            'file_name' => 'locandina-archivio.webp',
            'collection' => 'featured',
            'title' => 'Locandina d\'Archivio',
        ]);

        Livewire::actingAs($this->admin)
            ->test(CreateEvent::class)
            ->callFormComponentAction('featured_image', 'openPicker', data: [
                'selected_library_media' => [$media->file_path],
            ])
            ->assertHasNoFormComponentActionErrors()
            ->assertSet('data.featured_image', $media->file_path);
    }

    public function test_can_select_gallery_images_from_media_library(): void
    {
        $this->actingAs($this->admin);

        $media1 = MediaItem::create([
            'disk' => 'public',
            'file_path' => 'uploads/gallery-1.webp',
            'file_name' => 'gallery-1.webp',
            'collection' => 'gallery',
            'title' => 'Gallery 1',
        ]);

        $media2 = MediaItem::create([
            'disk' => 'public',
            'file_path' => 'uploads/gallery-2.webp',
            'file_name' => 'gallery-2.webp',
            'collection' => 'gallery',
            'title' => 'Gallery 2',
        ]);

        Livewire::actingAs($this->admin)
            ->test(CreateEvent::class)
            ->callFormComponentAction('gallery_images', 'openPicker', data: [
                'selected_library_media' => [$media1->file_path, $media2->file_path],
            ])
            ->assertHasNoFormComponentActionErrors()
            ->assertSet('data.gallery_images', [$media1->file_path, $media2->file_path]);
    }
}
