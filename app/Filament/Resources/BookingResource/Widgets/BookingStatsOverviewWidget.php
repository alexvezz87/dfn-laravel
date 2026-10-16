<?php

namespace App\Filament\Resources\BookingResource\Widgets;

use App\Models\Booking;
use App\Models\Event;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $activeBookingsCount = Booking::query()->where('status', 'confirmed')->count();
        $occupiedPlaces = (int) Booking::query()->where('status', 'confirmed')->sum('total_persons');
        $faiPendingCount = Booking::query()->where('status', 'pending_approval')->count();

        // Calcola la capienza totale degli eventi attivi/in programma
        $totalCapacity = (int) Event::query()->where('is_active', true)->sum('total_capacity');
        $occupancyRate = $totalCapacity > 0
            ? round(($occupiedPlaces / $totalCapacity) * 100, 1).'%'
            : ($occupiedPlaces > 0 ? 'N/D' : '0%');

        return [
            Stat::make('Prenotazioni Attive', $activeBookingsCount)
                ->description('Prenotazioni confermate a sistema')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('success'),

            Stat::make('Posti Occupati', $occupiedPlaces)
                ->description('Partecipanti totali confermati')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Tasso di Occupazione', $occupancyRate)
                ->description($totalCapacity > 0 ? "Su {$totalCapacity} posti totali previsti" : 'Capacità complessiva eventi attivi')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make('Tessere FAI da Verificare', $faiPendingCount)
                ->description($faiPendingCount > 0 ? 'Richieste in attesa di approvazione' : 'Nessuna richiesta pendente')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($faiPendingCount > 0 ? 'warning' : 'gray'),
        ];
    }
}
