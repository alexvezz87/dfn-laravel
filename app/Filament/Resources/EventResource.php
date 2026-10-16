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
                                    ->placeholder('Es: Visita al Castello Visconteo')
                                    ->helperText('Puoi modificare il titolo dell\'evento in qualsiasi momento. La modifica aggiornerà automaticamente la scheda pubblica.'),

                                Forms\Components\TextInput::make('product_id')
                                    ->label('Prodotto WooCommerce Collegato (Opzionale / Riferimento)')
                                    ->numeric()
                                    ->placeholder('Es. ID numerico (lascia vuoto per nuovi eventi)')
                                    ->helperText('Collega questa configurazione ad un eventuale Prodotto WooCommerce/ID catalogo pregresso. Per i nuovi eventi non è necessario generare un prodotto WooCommerce.'),
                            ]),

                        Forms\Components\Section::make('Logistica & Orari')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DatePicker::make('event_date_start')
                                            ->label('Data Inizio')
                                            ->native(false)
                                            ->required(),
                                        Forms\Components\DatePicker::make('event_date_end')
                                            ->label('Data Fine (Opzionale)')
                                            ->native(false),
                                        Forms\Components\TimePicker::make('event_time_start')
                                            ->label('Orario Apertura Inizio')
                                            ->seconds(false)
                                            ->required(),
                                        Forms\Components\TimePicker::make('event_time_end')
                                            ->label('Orario Chiusura Fine (Opzionale)')
                                            ->seconds(false),
                                    ]),

                                Forms\Components\DateTimePicker::make('booking_opening_date')
                                    ->label('Data e Ora Apertura Prenotazioni (Opzionale)')
                                    ->native(false)
                                    ->helperText('Se compilata, le prenotazioni rimarranno bloccate e un countdown indicherà agli utenti il momento esatto di apertura.'),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Textarea::make('location')
                                            ->label('Luogo dell\'Evento / Luogo di Ritrovo')
                                            ->required()
                                            ->rows(2)
                                            ->placeholder('Es: Basilica di San Gaudenzio - Via S. Gaudenzio, 22'),
                                        Forms\Components\TextInput::make('city')
                                            ->label('Comune / Città')
                                            ->placeholder('Es: Novara, Arona, Galliate...'),
                                    ]),

                                Forms\Components\RichEditor::make('description')
                                    ->label('Descrizione')
                                    ->helperText('Inserisci testo descrittivo, cenni storici, raccomandazioni per i partecipanti, ecc.')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Tipologia Accesso & Algoritmo di Allocazione')
                            ->icon('heroicon-o-users')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('access_type')
                                            ->label('Modalità di Accesso')
                                            ->options([
                                                'free_flow' => 'Flusso Libero (Senza fasce)',
                                                'time_slots' => 'Fasce Orarie (a turni)',
                                            ])
                                            ->default('free_flow')
                                            ->required()
                                            ->live(),

                                        Forms\Components\Select::make('allocation_mode')
                                            ->label('Algoritmo di Allocazione')
                                            ->options([
                                                'automatic' => 'Assegnazione Automatica (Default)',
                                                'self_selection' => 'Scelta autonoma utente',
                                            ])
                                            ->default('automatic')
                                            ->required(),
                                    ]),

                                Forms\Components\Fieldset::make('Parametri Flusso Libero')
                                    ->schema([
                                        Forms\Components\TextInput::make('total_capacity')
                                            ->label('Capacità Massima Evento')
                                            ->numeric()
                                            ->default(100)
                                            ->minValue(1)
                                            ->helperText('Capacità totale massima di persone ammesse all\'evento.'),
                                    ])
                                    ->visible(fn (Forms\Get $get): bool => $get('access_type') === 'free_flow'),

                                Forms\Components\Fieldset::make('Parametri Fasce Orarie')
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('slot_duration')
                                                    ->label('Durata Turno (min)')
                                                    ->numeric()
                                                    ->default(30)
                                                    ->minValue(5),
                                                Forms\Components\TextInput::make('slot_capacity')
                                                    ->label('Capacità Standard Turno')
                                                    ->numeric()
                                                    ->default(20)
                                                    ->minValue(1),
                                                Forms\Components\TextInput::make('slot_bonus')
                                                    ->label('Capacità Bonus (Staff/Live)')
                                                    ->numeric()
                                                    ->default(5)
                                                    ->minValue(0),
                                            ]),

                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TimePicker::make('first_slot_time')
                                                    ->label('Orario Primo Turno')
                                                    ->seconds(false)
                                                    ->default('10:00'),
                                                Forms\Components\TimePicker::make('last_slot_time')
                                                    ->label('Orario Ultimo Turno')
                                                    ->seconds(false)
                                                    ->default('18:00'),
                                            ]),
                                    ])
                                    ->visible(fn (Forms\Get $get): bool => $get('access_type') === 'time_slots'),
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
                                        'online' => 'Solo Pagamento Online',
                                        'in_loco' => 'Solo Pagamento in Loco',
                                        'both' => 'Entrambi (Online o in Loco)',
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
                                    ->placeholder('es. tua.email@dominio.it')
                                    ->visible(fn (Forms\Get $get): bool => (bool) $get('is_test_event')),

                                Forms\Components\Toggle::make('show_reviews_frontend')
                                    ->label('Mostra Recensioni nel Front-End')
                                    ->default(true)
                                    ->helperText('Se attivo, quando l\'evento è concluso mostra la valutazione media e le recensioni verificate sulla scheda dell\'evento.'),
                            ]),

                        Forms\Components\Section::make('Immagine in Evidenza')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Forms\Components\FileUpload::make('featured_image')
                                    ->label('Locandina Principale')
                                    ->image()
                                    ->disk('public')
                                    ->directory('events/featured')
                                    ->imageEditor()
                                    ->helperText('Locandina o copertina principale dell\'evento.'),
                            ]),

                        Forms\Components\Section::make('Galleria Slider (Pre-Evento)')
                            ->icon('heroicon-o-squares-2x2')
                            ->schema([
                                Forms\Components\FileUpload::make('gallery_images')
                                    ->label('Foto Slider')
                                    ->image()
                                    ->multiple()
                                    ->reorderable()
                                    ->disk('public')
                                    ->directory('events/gallery')
                                    ->helperText('Foto descrittive inserite nello slider della scheda evento (Solo immagini: jpg, png, webp).'),
                            ]),

                        Forms\Components\Section::make('Wall dei Ricordi (Post-Evento)')
                            ->icon('heroicon-o-video-camera')
                            ->schema([
                                Forms\Components\FileUpload::make('wall_media')
                                    ->label('Media Wall Ricordi')
                                    ->multiple()
                                    ->disk('public')
                                    ->directory('events/wall')
                                    ->acceptedFileTypes(['image/*', 'video/mp4', 'video/webm', 'video/ogg'])
                                    ->helperText('Carica foto e video (mp4, webm) registrati durante l\'evento per la vista conclusa.'),
                            ]),

                        Forms\Components\Section::make('Layout Pagina Dettaglio')
                            ->icon('heroicon-o-window')
                            ->schema([
                                Forms\Components\Radio::make('detail_layout')
                                    ->label('Layout Scheda Pubblica')
                                    ->options([
                                        'auto' => 'Automatico (Layout 2 se ha galleria, oppure 1 se locandina)',
                                        'layout1' => 'Layout 1 — Galleria (Slider in cima + form a destra)',
                                        'layout2' => 'Layout 2 — Locandina (Locandina a sinistra + form a destra)',
                                    ])
                                    ->default('auto')
                                    ->helperText('Scegli come visualizzare l\'evento nella pagina di dettaglio pubblica.'),
                            ]),

                        Forms\Components\Section::make('Listino Contributi')
                            ->icon('heroicon-o-currency-euro')
                            ->description('Contributo minimo per il rispettivo biglietto:')
                            ->schema([
                                Forms\Components\TextInput::make('price_standard')
                                    ->label('Biglietto Standard (€)')
                                    ->numeric()
                                    ->prefix('€')
                                    ->default(10.00)
                                    ->required()
                                    ->minValue(0)
                                    ->step(0.01),

                                Forms\Components\TextInput::make('price_fai')
                                    ->label('Socio FAI / Scontato (€)')
                                    ->numeric()
                                    ->prefix('€')
                                    ->placeholder('Es. 8.00')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Opzionale. Tariffa agevolata per soci FAI.'),
                            ]),

                        Forms\Components\Section::make('Staff & Note Interne')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                Forms\Components\Textarea::make('staff_config')
                                    ->label('Configurazione Staff / Istruzioni')
                                    ->rows(4)
                                    ->placeholder('Assegnazione autorizzazioni o istruzioni speciali per la cassa...')
                                    ->helperText('Note visibili solo allo staff per la gestione operativa.'),
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
