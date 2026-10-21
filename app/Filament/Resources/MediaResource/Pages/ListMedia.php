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
                ->label('Griglia')
                ->icon('heroicon-o-squares-2x2')
                ->color($this->viewMode === 'grid' ? 'primary' : 'gray')
                ->action(fn () => $this->setViewMode('grid')),

            Actions\Action::make('switchToList')
                ->label('Elenco')
                ->icon('heroicon-o-bars-3')
                ->color($this->viewMode === 'list' ? 'primary' : 'gray')
                ->action(fn () => $this->setViewMode('list')),

            Actions\CreateAction::make()
                ->label('Carica Nuovo Media')
                ->icon('heroicon-o-arrow-up-tray'),
        ];
    }
}
