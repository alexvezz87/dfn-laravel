<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Filament\Resources\EventResource\Pages\Concerns\OptimizesEventImages;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    use OptimizesEventImages;

    protected static string $resource = EventResource::class;

    public function getTitle(): string
    {
        return 'Configura Nuovo Evento FAI';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->optimizeMediaBeforeSave($data);
    }

    protected function afterCreate(): void
    {
        $this->optimizeRecordMedia();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Torna al Tabellone')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(EventResource::getUrl('index')),
        ];
    }

    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Crea Evento FAI')
            ->icon('heroicon-o-plus');
    }
}
