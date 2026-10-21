<?php

namespace Tests\Feature\Filament;

use App\Actions\OptimizeImageAction;
use App\Filament\Pages\ManageMediaSettings;
use App\Filament\Resources\EventResource;
use App\Models\Event;
use App\Models\Setting;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MediaSettingsTest extends TestCase
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

    public function test_can_access_media_settings_page(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(ManageMediaSettings::getUrl());

        $response->assertSuccessful();
    }

    public function test_can_save_media_settings(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ManageMediaSettings::class)
            ->fillForm([
                'media_max_image_mb' => 15,
                'media_max_video_mb' => 40,
                'media_auto_compress' => true,
                'media_max_dimension_px' => 1280,
                'media_quality_percent' => 75,
                'media_convert_webp' => false,
                'media_grid_columns' => 8,
            ])
            ->call('submit')
            ->assertHasNoFormErrors()
            ->assertNotified('Impostazioni media aggiornate con successo');

        $this->assertSame(15, Setting::getInt('media_max_image_mb'));
        $this->assertSame(40, Setting::getInt('media_max_video_mb'));
        $this->assertTrue(Setting::getBool('media_auto_compress'));
        $this->assertSame(1280, Setting::getInt('media_max_dimension_px'));
        $this->assertSame(75, Setting::getInt('media_quality_percent'));
        $this->assertFalse(Setting::getBool('media_convert_webp'));
        $this->assertSame(8, Setting::getInt('media_grid_columns'));
    }

    public function test_setting_helper_returns_default_values_when_empty(): void
    {
        $this->assertSame(8, Setting::getInt('media_max_image_mb'));
        $this->assertSame(50, Setting::getInt('media_max_video_mb'));
        $this->assertTrue(Setting::getBool('media_auto_compress'));
        $this->assertSame(1920, Setting::getInt('media_max_dimension_px'));
        $this->assertSame(82, Setting::getInt('media_quality_percent'));
        $this->assertTrue(Setting::getBool('media_convert_webp'));
        $this->assertSame(7, Setting::getInt('media_grid_columns', 7));
        $this->assertSame(99, Setting::getInt('non_existent_key', 99));
        $this->assertTrue(Setting::getBool('non_existent_bool', true));
    }

    public function test_setting_helper_returns_stored_values(): void
    {
        Setting::set('media_max_image_mb', 12, 'media');
        Setting::set('media_max_video_mb', 30, 'media');
        Setting::set('media_auto_compress', false, 'media');

        $this->assertSame(12, Setting::getInt('media_max_image_mb'));
        $this->assertSame(30, Setting::getInt('media_max_video_mb'));
        $this->assertFalse(Setting::getBool('media_auto_compress'));
        $this->assertSame('12', Setting::get('media_max_image_mb'));
    }

    public function test_event_form_allows_large_image_upload_when_auto_compress_is_enabled(): void
    {
        Setting::set('media_auto_compress', true, 'media');
        Setting::set('media_max_image_mb', 4, 'media');

        Livewire::actingAs($this->admin)
            ->test(EventResource\Pages\CreateEvent::class)
            ->assertFormFieldExists('featured_image', function (FileUpload $field): bool {
                return $field->getMaxSize() === 50 * 1024;
            })
            ->assertFormFieldExists('gallery_images', function (FileUpload $field): bool {
                return $field->getMaxSize() === 50 * 1024;
            });
    }

    public function test_event_form_enforces_strict_max_size_when_auto_compress_is_disabled(): void
    {
        Setting::set('media_auto_compress', false, 'media');
        Setting::set('media_max_image_mb', 4, 'media');
        Setting::set('media_max_video_mb', 35, 'media');

        Livewire::actingAs($this->admin)
            ->test(EventResource\Pages\CreateEvent::class)
            ->assertFormFieldExists('featured_image', function (FileUpload $field): bool {
                return $field->getMaxSize() === 4 * 1024;
            })
            ->assertFormFieldExists('gallery_images', function (FileUpload $field): bool {
                return $field->getMaxSize() === 4 * 1024;
            })
            ->assertFormFieldExists('wall_media', function (FileUpload $field): bool {
                return $field->getMaxSize() === 35 * 1024;
            });
    }

    public function test_optimize_image_action_resizes_and_converts_to_webp(): void
    {
        Storage::fake('public');

        // Crea un'immagine di test 2400x1200 via GD
        $img = imagecreatetruecolor(2400, 1200);
        $color = imagecolorallocate($img, 100, 150, 200);
        imagefilledrectangle($img, 0, 0, 2400, 1200, $color);

        ob_start();
        imagejpeg($img);
        $jpegData = ob_get_clean();
        imagedestroy($img);

        Storage::disk('public')->put('events/featured/test-large.jpg', $jpegData);

        Setting::set('media_auto_compress', true, 'media');
        Setting::set('media_max_dimension_px', 1920, 'media');
        Setting::set('media_convert_webp', true, 'media');

        $action = new OptimizeImageAction;
        $outputPath = $action->execute('events/featured/test-large.jpg', 'public');

        $this->assertSame('events/featured/test-large.webp', $outputPath);
        $this->assertTrue(Storage::disk('public')->exists('events/featured/test-large.webp'));

        // Verifica dimensioni ridimensionate
        $resultContents = Storage::disk('public')->get('events/featured/test-large.webp');
        $resultImg = imagecreatefromstring($resultContents);
        $this->assertNotFalse($resultImg);
        $this->assertSame(1920, imagesx($resultImg));
        $this->assertSame(960, imagesy($resultImg));
        imagedestroy($resultImg);

        // Verifica tracciamento byte e statistiche
        $this->assertGreaterThan(0, $action->getOriginalBytes());
        $this->assertGreaterThan(0, $action->getFinalBytes());
        $this->assertTrue($action->wasOptimized());
        $this->assertNotNull($action->getSavingsSummary());
    }

    public function test_create_and_edit_event_hooks_optimize_images_and_notify(): void
    {
        Storage::fake('public');

        $img = imagecreatetruecolor(2000, 1000);
        $color = imagecolorallocate($img, 50, 120, 200);
        imagefilledrectangle($img, 0, 0, 2000, 1000, $color);
        ob_start();
        imagejpeg($img);
        $jpegData = ob_get_clean();
        imagedestroy($img);

        Storage::disk('public')->put('events/featured/original-locandina.jpg', $jpegData);

        Setting::set('media_auto_compress', true, 'media');
        Setting::set('media_max_dimension_px', 1280, 'media');
        Setting::set('media_convert_webp', true, 'media');

        Livewire::actingAs($this->admin)
            ->test(EventResource\Pages\CreateEvent::class)
            ->fillForm([
                'title' => 'Concerto di Test Ottimizzazione',
                'event_date_start' => now()->addDays(5)->toDateString(),
                'event_time_start' => '18:00',
                'location' => 'Piazza Duomo',
                'price_standard' => 10.00,
                'price_fai' => 8.00,
                'featured_image' => UploadedFile::fake()->image('original-locandina.jpg', 2000, 1000),
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified('Immagine ottimizzata automaticamente');

        $event = Event::where('title', 'Concerto di Test Ottimizzazione')->first();
        $this->assertNotNull($event);
        $this->assertStringStartsWith('events/featured/', $event->featured_image);
        $this->assertStringEndsWith('.webp', $event->featured_image);
        $this->assertTrue(Storage::disk('public')->exists($event->featured_image));
    }
}
