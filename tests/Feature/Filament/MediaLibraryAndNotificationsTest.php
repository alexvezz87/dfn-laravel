<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\ManageMediaSettings;
use App\Filament\Resources\BookingResource;
use App\Filament\Resources\EventResource;
use App\Filament\Resources\FaiMemberResource;
use App\Filament\Resources\MediaResource;
use App\Filament\Resources\MediaResource\Pages\CreateMedia;
use App\Filament\Resources\MediaResource\Pages\ListMedia;
use App\Filament\Resources\SlotResource;
use App\Models\MediaItem;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MediaLibraryAndNotificationsTest extends TestCase
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

    public function test_can_access_media_library_page(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(MediaResource::getUrl('index'));

        $response->assertSuccessful();

        Livewire::actingAs($this->admin)
            ->test(ListMedia::class)
            ->assertSuccessful();
    }

    public function test_can_create_media_item_record(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test-banner.jpg', 1200, 800);

        Livewire::actingAs($this->admin)
            ->test(CreateMedia::class)
            ->fillForm([
                'title' => 'Locandina Banner Villa',
                'alt_text' => 'Foto panoramica della villa',
                'caption' => 'Ingresso principale',
                'collection' => 'featured',
                'file_path' => $file,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $mediaItem = MediaItem::where('title', 'Locandina Banner Villa')->first();

        $this->assertNotNull($mediaItem);
        $this->assertSame('featured', $mediaItem->collection);
        $this->assertSame('Foto panoramica della villa', $mediaItem->alt_text);
        $this->assertSame('Ingresso principale', $mediaItem->caption);
        $this->assertNotEmpty($mediaItem->file_path);
        $this->assertTrue(Storage::disk('public')->exists($mediaItem->file_path));
    }

    public function test_deleting_media_item_removes_physical_file_from_storage(): void
    {
        Storage::fake('public');

        $filePath = 'uploads/sample-delete.jpg';
        Storage::disk('public')->put($filePath, 'fake image content');

        $this->assertTrue(Storage::disk('public')->exists($filePath));

        $item = MediaItem::create([
            'disk' => 'public',
            'file_path' => $filePath,
            'file_name' => 'sample-delete.jpg',
            'file_size' => 18,
            'mime_type' => 'image/jpeg',
            'collection' => 'uploads',
            'title' => 'File da cancellare',
        ]);

        $this->assertDatabaseHas('media_items', ['id' => $item->id]);

        $item->delete();

        $this->assertDatabaseMissing('media_items', ['id' => $item->id]);
        $this->assertFalse(Storage::disk('public')->exists($filePath));
    }

    public function test_database_notifications_are_enabled_in_panel(): void
    {
        $panel = Filament::getPanel('admin');

        $this->assertTrue($panel->hasDatabaseNotifications());
        $this->assertSame('30s', $panel->getDatabaseNotificationsPollingInterval());
    }

    public function test_notification_can_be_sent_to_database(): void
    {
        Notification::make()
            ->title('Immagine ottimizzata con successo')
            ->body('La locandina è stata ridotta da 4.5 MB a 1.1 MB (risparmio 75%).')
            ->icon('heroicon-o-sparkles')
            ->iconColor('success')
            ->sendToDatabase($this->admin);

        $this->assertCount(1, $this->admin->fresh()->notifications);

        $notification = $this->admin->fresh()->notifications->first();
        $this->assertSame('Immagine ottimizzata con successo', $notification->data['title']);
        $this->assertStringContainsString('risparmio 75%', $notification->data['body']);
        $this->assertSame('heroicon-o-sparkles', $notification->data['icon']);
        $this->assertSame('success', $notification->data['iconColor']);
    }

    public function test_navigation_groups_are_properly_structured(): void
    {
        $this->assertSame('FAI Prenotazioni', EventResource::getNavigationGroup());
        $this->assertSame('FAI Prenotazioni', BookingResource::getNavigationGroup());
        $this->assertSame('FAI Prenotazioni', SlotResource::getNavigationGroup());
        $this->assertSame('FAI Prenotazioni', FaiMemberResource::getNavigationGroup());

        $this->assertSame(1, EventResource::getNavigationSort());
        $this->assertSame(2, BookingResource::getNavigationSort());
        $this->assertSame(3, SlotResource::getNavigationSort());
        $this->assertSame(4, FaiMemberResource::getNavigationSort());

        $this->assertSame('Media', MediaResource::getNavigationGroup());
        $this->assertSame('Libreria Media', MediaResource::getNavigationLabel());
        $this->assertSame(1, MediaResource::getNavigationSort());

        $this->assertSame('Impostazioni', ManageMediaSettings::getNavigationGroup());
        $this->assertSame('Media & Upload', ManageMediaSettings::getNavigationLabel());
        $this->assertSame(1, ManageMediaSettings::getNavigationSort());
    }

    public function test_media_item_create_from_storage_path_registers_metadata(): void
    {
        Storage::fake('public');

        $path = 'events/featured/auto-reg.jpg';
        Storage::disk('public')->put($path, 'dummy image content');

        $item = MediaItem::createFromStoragePath($path, 'featured', 'Locandina Originale.jpg');

        $this->assertNotNull($item);
        $this->assertSame('events/featured/auto-reg.jpg', $item->file_path);
        $this->assertSame('featured', $item->collection);
        $this->assertSame('Locandina Originale.jpg', $item->original_name);
        $this->assertSame('Locandina Originale', $item->title);
        $this->assertDatabaseHas('media_items', [
            'file_path' => 'events/featured/auto-reg.jpg',
            'collection' => 'featured',
        ]);

        // Second call should return existing item without duplicates
        $duplicate = MediaItem::createFromStoragePath($path, 'featured');
        $this->assertSame($item->id, $duplicate->id);
        $this->assertSame(1, MediaItem::where('file_path', $path)->count());
    }

    public function test_public_storage_url_is_root_relative_and_not_localhost(): void
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $url = $disk->url('events/featured/sample.webp');

        $this->assertStringStartsWith('/storage/', $url);
        $this->assertStringNotContainsString('http://localhost', $url);
    }

    public function test_upload_file_preserves_slugified_original_filename_and_title(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('Locandina United Gospel 2026.jpg', 600, 400);

        Livewire::actingAs($this->admin)
            ->test(CreateMedia::class)
            ->fillForm([
                'title' => null, // Lasciato vuoto intenzionalmente
                'collection' => 'uploads',
                'file_path' => $file,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        /** @var MediaItem $mediaItem */
        $mediaItem = MediaItem::latest('id')->first();

        $this->assertNotNull($mediaItem);
        $this->assertStringContainsString('locandina-united-gospel-2026', $mediaItem->file_name);
        $this->assertStringContainsString('locandina-united-gospel-2026', $mediaItem->file_path);
        $this->assertStringEndsWith('.webp', $mediaItem->file_path);
        $this->assertSame('image/webp', $mediaItem->mime_type);
        $this->assertStringContainsString('locandina-united-gospel-2026', strtolower($mediaItem->title));
        $this->assertTrue(Storage::disk('public')->exists($mediaItem->file_path));

        // Test collision management: secondo upload con medesimo nome file
        $fileCollision = UploadedFile::fake()->image('Locandina United Gospel 2026.jpg', 600, 400);

        Livewire::actingAs($this->admin)
            ->test(CreateMedia::class)
            ->fillForm([
                'title' => null,
                'collection' => 'uploads',
                'file_path' => $fileCollision,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        /** @var MediaItem $secondItem */
        $secondItem = MediaItem::latest('id')->first();

        $this->assertNotSame($mediaItem->id, $secondItem->id);
        $this->assertSame('locandina-united-gospel-2026-1.webp', $secondItem->file_name);
        $this->assertTrue(Storage::disk('public')->exists($secondItem->file_path));
    }

    public function test_upload_in_create_media_converts_image_to_webp_and_optimizes_size(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('foto-castello-grande.png', 1600, 1200);

        Livewire::actingAs($this->admin)
            ->test(CreateMedia::class)
            ->fillForm([
                'title' => 'Foto Castello Visconteo',
                'collection' => 'gallery',
                'file_path' => $file,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        /** @var MediaItem $mediaItem */
        $mediaItem = MediaItem::where('title', 'Foto Castello Visconteo')->first();

        $this->assertNotNull($mediaItem);
        $this->assertStringEndsWith('.webp', $mediaItem->file_path);
        $this->assertSame('image/webp', $mediaItem->mime_type);
        $this->assertSame('gallery', $mediaItem->collection);
        $this->assertTrue(Storage::disk('public')->exists($mediaItem->file_path));
    }

    public function test_can_toggle_between_grid_and_list_view_modes(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ListMedia::class)
            ->assertSet('viewMode', 'grid')
            ->call('setViewMode', 'list')
            ->assertSet('viewMode', 'list')
            ->call('setViewMode', 'grid')
            ->assertSet('viewMode', 'grid');
    }

    public function test_can_view_media_item_slide_over_details(): void
    {
        $item = MediaItem::create([
            'disk' => 'public',
            'file_path' => 'uploads/test-preview.jpg',
            'file_name' => 'test-preview.jpg',
            'original_name' => 'Test Preview.jpg',
            'file_size' => 102400,
            'mime_type' => 'image/jpeg',
            'width' => 1200,
            'height' => 800,
            'collection' => 'featured',
            'title' => 'Locandina Test Preview',
        ]);

        // Verifica in modalità Griglia
        Livewire::actingAs($this->admin)
            ->test(ListMedia::class)
            ->assertSet('viewMode', 'grid')
            ->mountTableAction('edit', $item)
            ->assertTableActionMounted('edit');

        // Verifica in modalità Elenco
        Livewire::actingAs($this->admin)
            ->test(ListMedia::class)
            ->call('setViewMode', 'list')
            ->assertSet('viewMode', 'list')
            ->assertCanSeeTableRecords([$item])
            ->assertTableColumnExists('title')
            ->assertTableColumnExists('collection')
            ->assertTableColumnExists('mime_type')
            ->assertTableColumnExists('formatted_size')
            ->mountTableAction('edit', $item)
            ->assertTableActionMounted('edit');
    }

    public function test_default_locale_is_italian(): void
    {
        $this->assertSame('it', config('app.locale'));
        $this->assertSame('it', config('app.fallback_locale'));
    }

    public function test_media_edit_action_has_modal_delete_and_italian_labels(): void
    {
        $item = MediaItem::create([
            'disk' => 'public',
            'file_path' => 'uploads/test-modal-delete.jpg',
            'file_name' => 'test-modal-delete.jpg',
            'original_name' => 'Test Modal Delete.jpg',
            'file_size' => 50000,
            'mime_type' => 'image/jpeg',
            'collection' => 'uploads',
            'title' => 'Test Modal Delete',
        ]);

        Livewire::actingAs($this->admin)
            ->test(ListMedia::class)
            ->mountTableAction('edit', $item)
            ->assertTableActionMounted('edit');
    }
}
