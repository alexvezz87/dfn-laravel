<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    public function getTitle(): string
    {
        return 'Modifica Evento FAI';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Anteprima Evento')
                ->icon('heroicon-o-eye')
                ->color('primary')
                ->url(fn (): string => route('event.preview', ['event' => $this->record->id]), shouldOpenInNewTab: true),

            Actions\Action::make('bookings')
                ->label('Tabellone Prenotazioni')
                ->icon('heroicon-o-ticket')
                ->color('gray')
                ->url(fn (): string => EventResource::getUrl('bookings', ['record' => $this->record])),

            Actions\Action::make('duplicate')
                ->label('Duplica questo Evento')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Duplica Evento')
                ->modalDescription('Verrà creata una copia identica di questo evento in stato Bozza con i relativi turni.')
                ->action(function (): void {
                    $replica = $this->record->replicate();
                    $replica->title = '(Copia) '.$this->record->title;
                    $replica->status = 'draft';
                    $replica->save();

                    foreach ($this->record->slots as $slot) {
                        $slotReplica = $slot->replicate();
                        $slotReplica->event_id = $replica->id;
                        $slotReplica->booked_count = 0;
                        $slotReplica->save();
                    }

                    Notification::make()
                        ->title('Evento duplicato con successo')
                        ->body('È stata creata la copia: '.$replica->title)
                        ->success()
                        ->send();

                    $this->redirect(EventResource::getUrl('edit', ['record' => $replica]));
                }),

            Actions\Action::make('back')
                ->label('Torna al Tabellone')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(EventResource::getUrl('index')),

            Actions\DeleteAction::make()
                ->label('Elimina'),
        ];
    }

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('Aggiorna Configurazione')
            ->icon('heroicon-o-check');
    }
}
