<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Models\Booking;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ManageEventBookings extends ManageRelatedRecords
{
    protected static string $resource = EventResource::class;

    protected static string $relationship = 'bookings';

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    public function getTitle(): string|Htmlable
    {
        return "Prenotazioni — {$this->getRecord()->title}";
    }

    public function getBreadcrumbs(): array
    {
        return [
            EventResource::getUrl('index') => 'Eventi',
            '#' => "Prenotazioni — {$this->getRecord()->title}",
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Torna agli Eventi')
                ->icon('heroicon-m-arrow-left')
                ->color('gray')
                ->url(EventResource::getUrl('index')),

            Actions\CreateAction::make()
                ->label('Nuova Prenotazione')
                ->icon('heroicon-m-plus')
                ->color('success')
                ->modalHeading("Aggiungi Prenotazione — {$this->getRecord()->title}")
                ->mutateFormDataUsing(function (array $data): array {
                    $data['event_id'] = $this->getRecord()->id;

                    return $data;
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EventResource\Widgets\EventBookingStatsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 4;
    }

    public function getHeader(): ?View
    {
        return null;
    }

    public function getTabs(): array
    {
        $activeCount = $this->getRecord()->bookings()->where('status', 'confirmed')->count();
        $historyCount = $this->getRecord()->bookings()->where('status', '!=', 'confirmed')->count();

        return [
            'active' => Tab::make('Prenotazioni Attive & Confermate')
                ->icon('heroicon-m-ticket')
                ->badge($activeCount)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'confirmed')),

            'history' => Tab::make('Storico Movimenti & Tentativi (Incompleti / Annullati)')
                ->icon('heroicon-m-exclamation-triangle')
                ->badge($historyCount)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '!=', 'confirmed')),

            'all' => Tab::make('Tutte le Prenotazioni')
                ->badge($this->getRecord()->bookings()->count()),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('customer_name')
                    ->label('Nome Cliente')
                    ->required(),
                Forms\Components\TextInput::make('customer_email')
                    ->label('Email')
                    ->email()
                    ->required(),
                Forms\Components\TextInput::make('customer_phone')
                    ->label('Telefono')
                    ->tel(),
                Forms\Components\TextInput::make('total_persons')
                    ->label('Totale Partecipanti')
                    ->numeric()
                    ->default(1)
                    ->required(),
                Forms\Components\TextInput::make('persons_standard')
                    ->label('Partecipanti Interi (Standard)')
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('persons_fai')
                    ->label('Soci FAI')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('amount_due')
                    ->label('Importo Dovuto')
                    ->numeric()
                    ->prefix('€')
                    ->default(0),
                Forms\Components\TextInput::make('amount_paid')
                    ->label('Importo Pagato')
                    ->numeric()
                    ->prefix('€')
                    ->default(0),
                Forms\Components\Select::make('status')
                    ->label('Stato Prenotazione')
                    ->options([
                        'confirmed' => 'Confermata',
                        'pending_approval' => 'In attesa approvazione FAI',
                        'pending_payment' => 'In attesa pagamento',
                        'cancelled' => 'Annullata',
                    ])
                    ->default('confirmed'),
                Forms\Components\Textarea::make('notes')
                    ->label('Note Interne')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        $bannerHtml = view('filament.resources.event-resource.pages.booking-slot-banner', [
            'record' => $this->getRecord(),
        ])->render();

        return $table
            ->description(new HtmlString($bannerHtml))
            ->columns([
                Tables\Columns\TextColumn::make('order_id')
                    ->label('Ordine #')
                    ->formatStateUsing(fn (?int $state, Booking $record): string => '#'.($state ?: $record->id))
                    ->weight('bold')
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (Booking $record): ?string => $record->customer_email),

                Tables\Columns\TextColumn::make('membership_type')
                    ->label('Qualifica')
                    ->badge()
                    ->state(fn (Booking $record): string => $record->persons_fai > 0 ? 'SOCIO FAI' : 'Standard')
                    ->color(fn (string $state): string => $state === 'SOCIO FAI' ? 'warning' : 'gray')
                    ->icon(fn (string $state): string => $state === 'SOCIO FAI' ? 'heroicon-m-identification' : 'heroicon-m-user')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('customer_phone')
                    ->label('Telefono')
                    ->url(fn (Booking $record): ?string => $record->customer_phone ? "tel:{$record->customer_phone}" : null)
                    ->color('info')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('total_persons')
                    ->label('Biglietti')
                    ->badge()
                    ->color('primary')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_info')
                    ->label('Pagamento')
                    ->badge()
                    ->state(fn (Booking $record): string => $record->amount_paid > 0 || $record->status === 'confirmed' ? '✔ Pagato' : 'In attesa')
                    ->color(fn (string $state): string => str_contains($state, 'Pagato') ? 'success' : 'warning')
                    ->description(fn (Booking $record): ?string => $record->payment_method ? ucfirst($record->payment_method) : null),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Prenotazione registrata')
                    ->dateTime('d/m/Y - H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Note')
                    ->placeholder('-')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
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
                            ->title('Check-in effettuato con successo')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('approve_fai')
                    ->label('Approva FAI')
                    ->icon('heroicon-o-check')
                    ->color('warning')
                    ->visible(fn (Booking $record): bool => $record->status === 'pending_approval')
                    ->requiresConfirmation()
                    ->action(function (Booking $record): void {
                        $record->update(['status' => 'confirmed']);

                        Notification::make()
                            ->title('Tessera verificata e confermata')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
