<?php

namespace App\Filament\Forms\Components;

use App\Actions\OptimizeImageAction;
use App\Models\MediaItem;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;

class WordpressMediaPicker extends Field
{
    protected string $view = 'filament.forms.components.wordpress-media-picker';

    protected bool $isMultiple = false;

    protected string $uploadDirectory = 'events/featured';

    protected string $collection = 'featured';

    protected ?int $maxSize = null;

    public function multiple(bool $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->isMultiple;
    }

    public function maxSize(?int $sizeInKb): static
    {
        $this->maxSize = $sizeInKb;

        return $this;
    }

    public function getMaxSize(): ?int
    {
        return $this->maxSize;
    }

    public function directory(string $dir): static
    {
        $this->uploadDirectory = $dir;

        return $this;
    }

    public function collection(string $collection): static
    {
        $this->collection = $collection;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerActions([
            fn (WordpressMediaPicker $component): Action => $component->getOpenPickerAction(),
        ]);
    }

    public function getOpenPickerAction(): Action
    {
        return Action::make('openPicker')
            ->label($this->isMultiple ? 'Aggiungi media' : 'Imposta immagine')
            ->modalWidth('6xl')
            ->modalHeading($this->isMultiple ? 'Galleria Media' : 'Immagine in Evidenza')
            ->modalSubmitActionLabel($this->isMultiple ? 'Aggiungi alla galleria' : 'Imposta come immagine in evidenza')
            ->modalCancelActionLabel('Annulla')
            ->modalFooterActionsAlignment(Alignment::End)
            ->form([
                Tabs::make('media_picker_tabs')
                    ->tabs([
                        Tabs\Tab::make('library')
                            ->label('Libreria media')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                ViewField::make('selected_library_media')
                                    ->label('')
                                    ->view('filament.forms.components.media-picker-grid')
                                    ->viewData([
                                        'isMultiple' => $this->isMultiple,
                                        'mediaItems' => MediaItem::query()->latest('id')->limit(120)->get(),
                                    ]),
                            ]),
                        Tabs\Tab::make('upload')
                            ->label('Carica file')
                            ->icon('heroicon-o-arrow-up-tray')
                            ->schema([
                                FileUpload::make('new_upload_file')
                                    ->label('Carica un nuovo file dal computer')
                                    ->disk('public')
                                    ->directory($this->uploadDirectory)
                                    ->image()
                                    ->multiple($this->isMultiple)
                                    ->maxSize($this->maxSize)
                                    ->helperText('Il file verrà automaticamente ottimizzato in formato WebP e archiviato nella Libreria Media.'),
                            ]),
                    ]),
            ])
            ->action(function (array $data, WordpressMediaPicker $component): void {
                $chosenPaths = [];

                // 1. Gestione file caricato dal Tab "Carica file"
                if (! empty($data['new_upload_file'])) {
                    $action = new OptimizeImageAction;
                    $uploads = (array) $data['new_upload_file'];

                    foreach ($uploads as $uploadedPath) {
                        if (is_string($uploadedPath) && $uploadedPath !== '') {
                            $optimizedPath = $action->execute($uploadedPath, 'public');
                            MediaItem::createFromStoragePath($optimizedPath, $component->collection);
                            $chosenPaths[] = $optimizedPath;
                        }
                    }
                }

                // 2. Gestione file selezionati dal Tab "Libreria media"
                if (! empty($data['selected_library_media'])) {
                    $selected = (array) $data['selected_library_media'];
                    foreach ($selected as $path) {
                        if (is_string($path) && $path !== '' && ! in_array($path, $chosenPaths, true)) {
                            $chosenPaths[] = $path;
                        }
                    }
                }

                if (empty($chosenPaths)) {
                    return;
                }

                if ($component->isMultiple()) {
                    $current = (array) ($component->getState() ?? []);
                    foreach ($chosenPaths as $path) {
                        if (! in_array($path, $current, true)) {
                            $current[] = $path;
                        }
                    }
                    $component->state($current);
                    Notification::make()
                        ->title(count($chosenPaths).' elemento/i aggiunto/i alla galleria')
                        ->success()
                        ->send();
                } else {
                    $component->state($chosenPaths[0]);
                    Notification::make()
                        ->title('Immagine in evidenza impostata con successo')
                        ->success()
                        ->send();
                }
            });
    }
}
