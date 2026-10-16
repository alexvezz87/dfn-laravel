<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'FAI Prenotazioni';

    protected static ?string $navigationLabel = 'Eventi';

    protected static ?string $modelLabel = 'Evento';

    protected static ?string $pluralModelLabel = 'Eventi';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('product_id')
                    ->numeric(),
                Forms\Components\TextInput::make('title'),
                Forms\Components\TextInput::make('access_type')
                    ->required(),
                Forms\Components\TextInput::make('allocation_mode'),
                Forms\Components\TextInput::make('approval_workflow'),
                Forms\Components\TextInput::make('payment_mode')
                    ->required(),
                Forms\Components\TextInput::make('price_standard')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('price_fai')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\DatePicker::make('event_date_start'),
                Forms\Components\DatePicker::make('event_date_end'),
                Forms\Components\TextInput::make('event_time_start'),
                Forms\Components\TextInput::make('event_time_end'),
                Forms\Components\TextInput::make('location'),
                Forms\Components\TextInput::make('city'),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('slot_duration')
                    ->numeric()
                    ->default(30),
                Forms\Components\TextInput::make('slot_capacity')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('slot_bonus')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('first_slot_time'),
                Forms\Components\TextInput::make('last_slot_time'),
                Forms\Components\TextInput::make('total_capacity')
                    ->numeric()
                    ->default(0),
                Forms\Components\Textarea::make('staff_config')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('status'),
                Forms\Components\TextInput::make('auto_cancel_hours')
                    ->required()
                    ->numeric()
                    ->default(24),
                Forms\Components\TextInput::make('detail_layout')
                    ->required(),
                Forms\Components\DateTimePicker::make('booking_opening_date'),
                Forms\Components\TextInput::make('booking_status')
                    ->required(),
                Forms\Components\Toggle::make('is_test_event')
                    ->required(),
                Forms\Components\TextInput::make('test_notification_email')
                    ->email(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Event $record): ?string => trim(($record->location ?? '').($record->city ? ' • '.$record->city : '')) ?: null)
                    ->wrap(),
                Tables\Columns\TextColumn::make('event_date_start')
                    ->label('Date & Orari')
                    ->formatStateUsing(function (Event $record): string {
                        $start = $record->event_date_start ? $record->event_date_start->format('d/m/Y') : '-';
                        $end = $record->event_date_end ? $record->event_date_end->format('d/m/Y') : $start;
                        $dates = ($start === $end) ? $start : "{$start} - {$end}";

                        return $dates;
                    })
                    ->description(fn (Event $record): ?string => $record->event_time_start ? substr($record->event_time_start, 0, 5).' - '.substr($record->event_time_end ?? '', 0, 5) : null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('access_type')
                    ->label('Tipologia')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'time_slots' => 'Fasce Orarie',
                        'free_flow' => 'Flusso Libero',
                        default => $state ?? 'Standard',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'time_slots' => 'info',
                        'free_flow' => 'primary',
                        default => 'gray',
                    })
                    ->description(fn (Event $record): ?string => match ($record->allocation_mode) {
                        'auto' => 'Allocazione auto',
                        'manual', 'self_selection' => 'Self-selection',
                        default => null,
                    }),
                Tables\Columns\TextColumn::make('pricing')
                    ->label('Tariffe')
                    ->state(fn (Event $record): string => 'Std: €'.number_format((float) $record->price_standard, 2, ',', '.').' • FAI: €'.number_format((float) $record->price_fai, 2, ',', '.')),
                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacità')
                    ->state(function (Event $record): string {
                        $booked = (int) $record->bookings()->where('status', 'confirmed')->sum('total_persons');
                        $total = $record->total_capacity ?: ($record->slots()->sum('capacity') ?: 0);

                        return $total > 0 ? "{$booked} / {$total}" : (string) $booked;
                    })
                    ->badge()
                    ->color('primary')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'published' => 'Pubblicato',
                        'draft' => 'Bozza',
                        'archived' => 'Archiviato',
                        default => $state ?? 'Bozza',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'gray',
                        'archived' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Attivo')
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('event_date_start', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Stato')
                    ->options([
                        'published' => 'Pubblicato',
                        'draft' => 'Bozza',
                        'archived' => 'Archiviato',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Attivo Online')
                    ->trueLabel('Attivo')
                    ->falseLabel('Disattivato'),
            ])
            ->actions([
                Tables\Actions\Action::make('bookings')
                    ->label('Prenotazioni')
                    ->icon('heroicon-o-ticket')
                    ->color('success')
                    ->url(fn (Event $record): string => EventResource::getUrl('bookings', ['record' => $record])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
            'bookings' => Pages\ManageEventBookings::route('/{record}/bookings'),
        ];
    }
}
