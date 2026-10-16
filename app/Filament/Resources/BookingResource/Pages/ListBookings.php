<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuova Prenotazione')
                ->icon('heroicon-m-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BookingResource\Widgets\BookingStatsOverviewWidget::class,
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'active';
    }

    public function getTabs(): array
    {
        $activeCount = Booking::query()->whereIn('status', ['confirmed', 'pending_approval'])->count();
        $faiCount = Booking::query()->where('status', 'pending_approval')->count();
        $historyCount = Booking::query()->whereNotIn('status', ['confirmed', 'pending_approval'])->count();

        return [
            'active' => Tab::make('Prenotazioni Attive & Confermate')
                ->icon('heroicon-m-check-circle')
                ->badge($activeCount)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['confirmed', 'pending_approval'])),

            'fai_pending' => Tab::make('Verifica FAI')
                ->icon('heroicon-m-exclamation-triangle')
                ->badge($faiCount)
                ->badgeColor($faiCount > 0 ? 'warning' : 'gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending_approval')),

            'history' => Tab::make('Storico Annullate & Tentativi')
                ->icon('heroicon-m-clock')
                ->badge($historyCount)
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotIn('status', ['confirmed', 'pending_approval'])),

            'all' => Tab::make('Tutte le Prenotazioni')
                ->badge(Booking::query()->count()),
        ];
    }
}
