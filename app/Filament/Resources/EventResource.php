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
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Titolo Evento & Associazione Prodotto')
                            ->icon('heroicon-o-pencil-square')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Titolo dell\'Evento / Biglietto')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Puoi modificare il titolo dell\'evento in qualsiasi momento. La modifica aggiornerà automaticamente anche il nome del Prodotto WooCommerce/Stripe collegato.'),

                                Forms\Components\TextInput::make('product_id')
                                    ->label('ID Prodotto WooCommerce / Riferimento Collegato')
                                    ->numeric()
                                    ->helperText('Collega questa configurazione di turni e listino ad un Prodotto WooCommerce esistente. Il prodotto funge da carrello per il check-out.'),
                            ]),

                        Forms\Components\Section::make('Logistica & Orari')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DatePicker::make('event_date_start')
                                            ->label('Data Inizio')
                                            ->required(),
                                        Forms\Components\DatePicker::make('event_date_end')
                                            ->label('Data Fine (Opzionale)'),
                                        Forms\Components\TimePicker::make('event_time_start')
                                            ->label('Orario Apertura Inizio')
                                            ->required(),
                                        Forms\Components\TimePicker::make('event_time_end')
                                            ->label('Orario Chiusura Fine (Opzionale)'),
                                    ]),

                                Forms\Components\DateTimePicker::make('booking_opening_date')
                                    ->label('Data e Ora Apertura Prenotazioni (Opzionale)')
                                    ->helperText('Se compilata, le prenotazioni rimarranno bloccate e un countdown indicherà agli utenti il momento esatto di apertura.'),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('location')
                                            ->label('Luogo di Ritrovo')
                                            ->placeholder('es. Quadriportico della Canonica, Vicolo Canonica 9'),
                                        Forms\Components\TextInput::make('city')
                                            ->label('Città')
                                            ->placeholder('es. Novara'),
                                    ]),

                                Forms\Components\RichEditor::make('description')
                                    ->label('Descrizione e Note dell\'Evento')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Tipologia Accesso, Capienza & Tariffe')
                            ->icon('heroicon-o-ticket')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\Select::make('access_type')
                                            ->label('Tipologia Accesso')
                                            ->options([
                                                'free_flow' => 'Flusso Libero (senza turni fissi)',
                                                'time_slots' => 'Fasce Orarie (a turni)',
                                            ])
                                            ->default('free_flow')
                                            ->required()
                                            ->live(),

                                        Forms\Components\Select::make('allocation_mode')
                                            ->label('Modalità Allocazione')
                                            ->options([
                                                'automatic' => 'Automatica dal sistema',
                                                'self_selection' => 'Self-selection (l\'utente sceglie il turno)',
                                            ])
                                            ->default('automatic'),

                                        Forms\Components\TextInput::make('total_capacity')
                                            ->label('Capacità Totale Posti')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Posti massimi complessivi previsti.'),

                                        Forms\Components\TextInput::make('price_standard')
                                            ->label('Quota Intera (Standard)')
                                            ->numeric()
                                            ->prefix('€')
                                            ->default(0.00)
                                            ->required(),

                                        Forms\Components\TextInput::make('price_fai')
                                            ->label('Quota Ridotta Socio FAI')
                                            ->numeric()
                                            ->prefix('€')
                                            ->default(0.00),

                                        Forms\Components\TextInput::make('slot_duration')
                                            ->label('Durata Slot (minuti)')
                                            ->numeric()
                                            ->default(30)
                                            ->visible(fn (Forms\Get $get): bool => $get('access_type') === 'time_slots'),
                                    ]),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Pubblica')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Stato Evento')
                                    ->options([
                                        'published' => 'Pubblicato (Attivo)',
                                        'draft' => 'Bozza (Non visibile)',
                                        'archived' => 'Archiviato',
                                    ])
                                    ->default('published')
                                    ->required(),

                                Forms\Components\Select::make('approval_workflow')
                                    ->label('Workflow Approvazione')
                                    ->options([
                                        'auto' => 'Conferma Automatica',
                                        'manual' => 'Richiede Approvazione Manuale FAI',
                                    ])
                                    ->default('auto'),

                                Forms\Components\Select::make('booking_status')
                                    ->label('Stato Modalità Prenotazione')
                                    ->options([
                                        'open' => 'Prenotazioni Aperte (Form Online)',
                                        'closed' => 'Prenotazioni Chiuse / Sold Out',
                                        'upcoming' => 'In Arrivo / Countdown',
                                    ])
                                    ->default('open'),

                                Forms\Components\Select::make('payment_mode')
                                    ->label('Modalità di Pagamento')
                                    ->options([
                                        'online' => 'Solo Pagamento Online (Stripe)',
                                        'in_loco' => 'Solo Pagamento in Loco',
                                        'both' => 'Online o in Loco',
                                    ])
                                    ->default('online'),

                                Forms\Components\TextInput::make('auto_cancel_hours')
                                    ->label('Annullamento Automatico (ore)')
                                    ->numeric()
                                    ->default(24)
                                    ->helperText('Dopo quante ore un ordine non pagato viene annullato automaticamente. Imposta 0 per disabilitare.'),

                                Forms\Components\Toggle::make('is_test_event')
                                    ->label('Modalità Evento di Test')
                                    ->helperText('Se attivo, le notifiche per lo Staff verranno inviate solo all\'email di test.')
                                    ->live(),

                                Forms\Components\TextInput::make('test_notification_email')
                                    ->label('Email Notifiche di Test')
                                    ->email()
                                    ->visible(fn (Forms\Get $get): bool => (bool) $get('is_test_event')),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Visibile Online')
                                    ->default(true),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
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
                        $total = $record->total_capacity ?: ($record->slots()->sum('max_capacity') ?: 0);

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
