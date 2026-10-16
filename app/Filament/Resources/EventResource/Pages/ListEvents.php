<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Models\Event;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuovo Evento')
                ->icon('heroicon-m-plus'),
        ];
    }

    public function getTabs(): array
    {
        $today = now()->startOfDay()->toDateString();

        $upcomingCount = Event::query()
            ->where(function (Builder $query) use ($today): void {
                $query->whereDate('event_date_end', '>=', $today)
                    ->orWhere(function (Builder $q) use ($today): void {
                        $q->whereNull('event_date_end')
                            ->whereDate('event_date_start', '>=', $today);
                    });
            })
            ->count();

        $pastCount = Event::query()
            ->where(function (Builder $query) use ($today): void {
                $query->where(function (Builder $q) use ($today): void {
                    $q->whereNotNull('event_date_end')
                        ->whereDate('event_date_end', '<', $today);
                })->orWhere(function (Builder $q) use ($today): void {
                    $q->whereNull('event_date_end')
                        ->whereDate('event_date_start', '<', $today);
                });
            })
            ->count();

        return [
            'upcoming' => Tab::make('Eventi in Programma & In Corso')
                ->icon('heroicon-m-calendar-days')
                ->badge($upcomingCount)
                ->badgeColor('success')
                ->modifyQueryUsing(function (Builder $query) use ($today): Builder {
                    return $query->where(function (Builder $q) use ($today): void {
                        $q->whereDate('event_date_end', '>=', $today)
                            ->orWhere(function (Builder $sub) use ($today): void {
                                $sub->whereNull('event_date_end')
                                    ->whereDate('event_date_start', '>=', $today);
                            });
                    })->orderBy('event_date_start', 'asc');
                }),

            'past' => Tab::make('Eventi Conclusi & Passati')
                ->icon('heroicon-m-archive-box')
                ->badge($pastCount)
                ->badgeColor('gray')
                ->modifyQueryUsing(function (Builder $query) use ($today): Builder {
                    return $query->where(function (Builder $q) use ($today): void {
                        $q->where(function (Builder $sub) use ($today): void {
                            $sub->whereNotNull('event_date_end')
                                ->whereDate('event_date_end', '<', $today);
                        })->orWhere(function (Builder $sub) use ($today): void {
                            $sub->whereNull('event_date_end')
                                ->whereDate('event_date_start', '<', $today);
                        });
                    })->orderBy('event_date_start', 'desc');
                }),

            'all' => Tab::make('Tutti gli Eventi')
                ->badge(Event::query()->count()),
        ];
    }
}
