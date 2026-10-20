<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageMediaSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'FAI Prenotazioni';

    protected static ?string $navigationLabel = 'Impostazioni Media & Upload';

    protected static ?string $title = 'Configurazione Media, Upload & Ottimizzazione';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.manage-media-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'media_max_image_mb' => Setting::getInt('media_max_image_mb', 8),
            'media_max_video_mb' => Setting::getInt('media_max_video_mb', 50),
            'media_auto_compress' => Setting::getBool('media_auto_compress', true),
            'media_max_dimension_px' => Setting::getInt('media_max_dimension_px', 1920),
            'media_quality_percent' => Setting::getInt('media_quality_percent', 82),
            'media_convert_webp' => Setting::getBool('media_convert_webp', true),
        ]);
    }

    public function getTitle(): string
    {
        return 'Configurazione Media, Upload & Ottimizzazione';
    }

    public function getSubheading(): ?string
    {
        return 'Regola le dimensioni massime di caricamento e i parametri di compressione automatica delle immagini.';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Limiti di Caricamento (Upload)')
                    ->description('Configura le dimensioni massime per file immagine e video.')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->schema([
                        TextInput::make('media_max_image_mb')
                            ->label('Dimensione Massima Immagini')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(50)
                            ->suffix('MB')
                            ->helperText('Dimensione massima per locandina principale e foto slider. Tetto server: 50 MB'),

                        TextInput::make('media_max_video_mb')
                            ->label('Dimensione Massima Video')
                            ->numeric()
                            ->required()
                            ->minValue(5)
                            ->maxValue(50)
                            ->suffix('MB')
                            ->helperText('Dimensione massima per i video caricati nel Wall dei Ricordi'),
                    ])
                    ->columns(2),

                Section::make('Compressione & Ottimizzazione Automatica')
                    ->description('Imposta la qualità di compressione e la risoluzione massima delle immagini caricate.')
                    ->icon('heroicon-o-sparkles')
                    ->schema([
                        Toggle::make('media_auto_compress')
                            ->label('Attiva compressione e ottimizzazione automatica immagini')
                            ->helperText('Ridimensiona e comprime in tempo reale evitando il caricamento di file pesanti.')
                            ->default(true),

                        Select::make('media_max_dimension_px')
                            ->label('Risoluzione Massima')
                            ->options([
                                1280 => 'HD (1280px)',
                                1920 => 'Full HD (1920px) — Consigliato',
                                2560 => '2K (2560px)',
                            ])
                            ->default(1920)
                            ->required()
                            ->helperText('Risoluzione massima (lato lungo) per le immagini caricate.'),

                        TextInput::make('media_quality_percent')
                            ->label('Qualità Compressione')
                            ->numeric()
                            ->required()
                            ->minValue(50)
                            ->maxValue(95)
                            ->suffix('%')
                            ->default(82)
                            ->helperText('Percentuale di qualità per i file generati (raccomandato 82%).'),

                        Toggle::make('media_convert_webp')
                            ->label('Converti automaticamente in WebP')
                            ->helperText('Velocizza sensibilmente il caricamento delle pagine pubbliche convertendo i formati standard in WebP.')
                            ->default(true),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();

        Setting::set('media_max_image_mb', $state['media_max_image_mb'], 'media');
        Setting::set('media_max_video_mb', $state['media_max_video_mb'], 'media');
        Setting::set('media_auto_compress', (bool) $state['media_auto_compress'], 'media');
        Setting::set('media_max_dimension_px', $state['media_max_dimension_px'], 'media');
        Setting::set('media_quality_percent', $state['media_quality_percent'], 'media');
        Setting::set('media_convert_webp', (bool) $state['media_convert_webp'], 'media');

        Notification::make()
            ->title('Impostazioni media aggiornate con successo')
            ->success()
            ->send();
    }

    public function save(): void
    {
        $this->submit();
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Salva Impostazioni')
                ->submit('submit'),
        ];
    }
}
