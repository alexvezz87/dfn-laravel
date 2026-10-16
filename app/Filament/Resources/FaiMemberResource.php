<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaiMemberResource\Pages;
use App\Models\FaiMember;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FaiMemberResource extends Resource
{
    protected static ?string $model = FaiMember::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'FAI Prenotazioni';

    protected static ?string $navigationLabel = 'Soci FAI';

    protected static ?string $modelLabel = 'Socio FAI';

    protected static ?string $pluralModelLabel = 'Soci FAI';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dati Anagrafici & Tessera')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('card_number')
                            ->label('Numero Tessera FAI')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('status')
                            ->label('Stato Tessera')
                            ->options([
                                'active' => 'Attivo / Valido',
                                'inactive' => 'Inattivo / Scaduto',
                            ])
                            ->default('active')
                            ->required(),
                        Forms\Components\TextInput::make('first_name')
                            ->label('Nome')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Cognome')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefono')
                            ->tel()
                            ->maxLength(50),
                        Forms\Components\DatePicker::make('expiry_date')
                            ->label('Data di Scadenza'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('card_number')
                    ->label('N° Tessera')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Numero tessera copiato')
                    ->badge()
                    ->color('warning')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nominativo Socio')
                    ->state(fn (FaiMember $record): string => trim("{$record->first_name} {$record->last_name}") ?: '—')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['last_name', 'first_name'])
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->url(fn (?string $state): ?string => $state ? "mailto:{$state}" : null)
                    ->color('primary')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefono')
                    ->searchable()
                    ->url(fn (?string $state): ?string => $state ? "tel:{$state}" : null)
                    ->color('info')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Scadenza')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(function (?string $state): string {
                        if (! $state) {
                            return 'gray';
                        }

                        return strtotime($state) >= strtotime('today') ? 'success' : 'danger';
                    })
                    ->placeholder('Non specificata'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Attivo',
                        'inactive' => 'Inattivo',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrato il')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Stato')
                    ->options([
                        'active' => 'Attivo',
                        'inactive' => 'Inattivo',
                    ]),
                Tables\Filters\Filter::make('valid_cards')
                    ->label('Solo Tessere Valide')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'active')->whereDate('expiry_date', '>=', now()->toDateString())),
                Tables\Filters\Filter::make('expired_cards')
                    ->label('Solo Tessere Scadute')
                    ->query(fn (Builder $query): Builder => $query->whereDate('expiry_date', '<', now()->toDateString())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListFaiMembers::route('/'),
            'create' => Pages\CreateFaiMember::route('/create'),
            'edit' => Pages\EditFaiMember::route('/{record}/edit'),
        ];
    }
}
