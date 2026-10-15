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

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

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
                Tables\Columns\TextColumn::make('product_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('access_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('allocation_mode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('approval_workflow')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_mode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price_standard')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_fai')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('event_date_start')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('event_date_end')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('event_time_start'),
                Tables\Columns\TextColumn::make('event_time_end'),
                Tables\Columns\TextColumn::make('location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slot_duration')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slot_capacity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slot_bonus')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_slot_time'),
                Tables\Columns\TextColumn::make('last_slot_time'),
                Tables\Columns\TextColumn::make('total_capacity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('auto_cancel_hours')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('detail_layout')
                    ->searchable(),
                Tables\Columns\TextColumn::make('booking_opening_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('booking_status')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_test_event')
                    ->boolean(),
                Tables\Columns\TextColumn::make('test_notification_email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
        ];
    }
}
