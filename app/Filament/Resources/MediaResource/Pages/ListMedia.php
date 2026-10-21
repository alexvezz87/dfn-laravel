<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    public string $viewMode = 'grid';

    public function getTitle(): string
    {
        return 'Libreria Media & File';
    }

    public function isGridView(): bool
    {
        return $this->viewMode === 'grid';
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['grid', 'list'], true) ? $mode : 'grid';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('switchToGrid')
                ->label('')
                ->icon('heroicon-o-squares-2x2')
                ->tooltip('Vista Griglia')
                ->color($this->viewMode === 'grid' ? 'primary' : 'gray')
                ->outlined($this->viewMode !== 'grid')
                ->action(fn () => $this->setViewMode('grid')),

            Actions\Action::make('switchToList')
                ->label('')
                ->icon('heroicon-o-bars-3')
                ->tooltip('Vista Elenco')
                ->color($this->viewMode === 'list' ? 'primary' : 'gray')
                ->outlined($this->viewMode !== 'list')
                ->action(fn () => $this->setViewMode('list')),

            Actions\CreateAction::make()
                ->label('Carica Nuovo Media')
                ->icon('heroicon-o-arrow-up-tray'),
        ];
    }
}
