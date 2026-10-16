<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Prenotazioni';

    protected static ?string $modelLabel = 'Prenotazione';

    protected static ?string $pluralModelLabel = 'Prenotazioni';

    protected static ?string $navigationGroup = 'Gestione Eventi';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dettagli Evento & Intestatario')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->label('Evento')
                            ->relationship('event', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->label('Utente Registrato')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Nome Intestatario')
                            ->maxLength(255)
                            ->required(),
                        Forms\Components\TextInput::make('customer_email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->required(),
                        Forms\Components\TextInput::make('customer_phone')
                            ->label('Telefono')
                            ->tel()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('order_id')
                            ->label('ID Ordine (WooCommerce / Riferimento)')
                            ->numeric(),
                    ]),

                Forms\Components\Section::make('Partecipanti & Biglietti')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('total_persons')
                            ->label('Totale Partecipanti')
                            ->numeric()
                            ->required()
                            ->default(1),
                        Forms\Components\TextInput::make('persons_standard')
                            ->label('Partecipanti Interi (Standard)')
                            ->numeric()
                            ->required()
                            ->default(1),
                        Forms\Components\TextInput::make('persons_fai')
                            ->label('Soci FAI')
                            ->numeric()
                            ->required()
                            ->default(0),
                    ]),

                Forms\Components\Section::make('Contabilità & Pagamento')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('amount_due')
                            ->label('Importo Dovuto')
                            ->numeric()
                            ->prefix('€')
                            ->required()
                            ->default(0),
                        Forms\Components\TextInput::make('amount_paid')
                            ->label('Importo Pagato')
                            ->numeric()
                            ->prefix('€')
                            ->required()
                            ->default(0),
                        Forms\Components\TextInput::make('payment_method')
                            ->label('Metodo di Pagamento')
                            ->maxLength(100),
                    ]),

                Forms\Components\Section::make('Stato & Check-in')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Stato Prenotazione')
                            ->options([
                                'confirmed' => 'Confermata',
                                'pending_approval' => 'In attesa approvazione FAI',
                                'pending_payment' => 'In attesa pagamento',
                                'cancelled' => 'Annullata',
                            ])
                            ->default('confirmed')
                            ->required(),
                        Forms\Components\Select::make('checkin_status')
                            ->label('Stato Check-in')
                            ->options([
                                'pending' => 'Da effettuare',
                                'completed' => 'Effettuato',
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\DateTimePicker::make('checkin_at')
                            ->label('Data e Ora Check-in'),
                        Forms\Components\TextInput::make('qr_code_hash')
                            ->label('Token / Hash QR Code')
                            ->maxLength(128)
                            ->disabled(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Note Interne')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#ID')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('event.title')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->limit(25),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Intestatario')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Booking $record): ?string => $record->customer_email),
                Tables\Columns\TextColumn::make('total_persons')
                    ->label('Tot. Pax')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('persons_standard')
                    ->label('Std')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('persons_fai')
                    ->label('FAI')
                    ->badge()
                    ->color('warning')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('amount_due')
                    ->label('Totale')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'pending_approval' => 'warning',
                        'pending_payment' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'confirmed' => 'Confermata',
                        'pending_approval' => 'In attesa FAI',
                        'pending_payment' => 'In attesa pag.',
                        'cancelled' => 'Annullata',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('checkin_at')
                    ->label('Check-in')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Non effettuato')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data Creazione')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Evento')
                    ->relationship('event', 'title'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Stato Prenotazione')
                    ->options([
                        'confirmed' => 'Confermata',
                        'pending_approval' => 'In attesa approvazione FAI',
                        'pending_payment' => 'In attesa pagamento',
                        'cancelled' => 'Annullata',
                    ]),
                Tables\Filters\TernaryFilter::make('checkin_status')
                    ->label('Check-in')
                    ->placeholder('Tutti')
                    ->trueLabel('Check-in Effettuato')
                    ->falseLabel('Da Effettuare')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('checkin_at'),
                        false: fn (Builder $query): Builder => $query->whereNull('checkin_at'),
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('checkin')
                    ->label('Check-in')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Booking $record): bool => is_null($record->checkin_at) && $record->status === 'confirmed')
                    ->requiresConfirmation()
                    ->action(function (Booking $record): void {
                        $record->update([
                            'checkin_at' => now(),
                            'checkin_status' => 'completed',
                        ]);

                        Notification::make()
                            ->title('Check-in registrato con successo')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('undo_checkin')
                    ->label('Annulla')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('gray')
                    ->visible(fn (Booking $record): bool => ! is_null($record->checkin_at))
                    ->requiresConfirmation()
                    ->action(function (Booking $record): void {
                        $record->update([
                            'checkin_at' => null,
                            'checkin_status' => 'pending',
                        ]);

                        Notification::make()
                            ->title('Check-in annullato')
                            ->warning()
                            ->send();
                    }),
                Tables\Actions\Action::make('approve_fai')
                    ->label('Approva FAI')
                    ->icon('heroicon-o-check')
                    ->color('warning')
                    ->visible(fn (Booking $record): bool => $record->status === 'pending_approval')
                    ->requiresConfirmation()
                    ->action(function (Booking $record): void {
                        $record->update([
                            'status' => 'confirmed',
                        ]);

                        Notification::make()
                            ->title('Tessera verificata: prenotazione confermata')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'view' => Pages\ViewBooking::route('/{record}'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
