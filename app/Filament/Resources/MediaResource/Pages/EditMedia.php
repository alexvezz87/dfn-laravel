<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;

    public function getTitle(): string
    {
        return 'Modifica Metadati Media';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Torna alla Libreria')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(MediaResource::getUrl('index')),

            Actions\DeleteAction::make()
                ->label('Elimina'),
        ];
    }

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('Salva Metadati')
            ->icon('heroicon-o-check');
    }
}
