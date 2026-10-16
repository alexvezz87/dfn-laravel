<?php

namespace App\Filament\Resources\EventResource\Widgets;

use App\Models\Event;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EventBookingStatsWidget extends BaseWidget
{
    public ?Event $record = null;

    protected function getStats(): array
    {
        if (! $this->record) {
            return [];
        }

        $activeBookingsCount = $this->record->bookings()->where('status', 'confirmed')->count();
        $occupiedPlaces = (int) $this->record->bookings()->where('status', 'confirmed')->sum('total_persons');
        $faiPendingCount = $this->record->bookings()->where('status', 'pending_approval')->count();

        $totalCapacity = (int) $this->record->total_capacity;
        if ($totalCapacity <= 0) {
            $totalCapacity = (int) $this->record->slots()->sum('max_capacity');
        }

        $occupancyRate = $totalCapacity > 0
            ? round(($occupiedPlaces / $totalCapacity) * 100, 1).'%'
            : ($occupiedPlaces > 0 ? 'N/D' : '0%');

        return [
            Stat::make('PRENOTAZIONI ATTIVE', $activeBookingsCount)
                ->description('Prenotazioni confermate per questo evento')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('success'),

            Stat::make('POSTI OCCUPATI', $occupiedPlaces)
                ->description('Partecipanti registrati')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('TASSO DI OCCUPAZIONE', $occupancyRate)
                ->description($totalCapacity > 0 ? "{$occupiedPlaces} / {$totalCapacity} posti totali" : 'Capacità totale configurata')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make('TESSERE FAI DA VERIFICARE', $faiPendingCount)
                ->description($faiPendingCount > 0 ? 'In attesa di approvazione' : 'Nessuna richiesta pendente')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($faiPendingCount > 0 ? 'warning' : 'gray'),
        ];
    }
}
