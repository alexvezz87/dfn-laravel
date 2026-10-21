<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\MediaItem;
use Filament\Forms;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MediaResource extends Resource
{
    protected static ?string $model = MediaItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Media';

    protected static ?string $navigationLabel = 'Libreria Media';

    protected static ?string $modelLabel = 'File multimediale';

    protected static ?string $pluralModelLabel = 'Libreria Media';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Group::make([
                            Forms\Components\Section::make('File & Anteprima')
                                ->schema([
                                    Forms\Components\FileUpload::make('file_path')
                                        ->label('File Multimediale')
                                        ->disk('public')
                                        ->directory('uploads')
                                        ->required()
                                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                                        ->dehydrated(fn (string $operation): bool => $operation !== 'edit')
                                        ->helperText('Carica immagini (JPG, PNG, WebP) o video (MP4, WebM) fino a 50 MB.')
                                        ->getUploadedFileNameForStorageUsing(function (BaseFileUpload $component, TemporaryUploadedFile $file): string {
                                            $originalBase = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                                            $slug = Str::slug($originalBase) ?: 'media';
                                            $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
                                            $dir = $component->getDirectory();
                                            $diskName = $component->getDiskName();
                                            /** @var FilesystemAdapter $storage */
                                            $storage = Storage::disk($diskName);

                                            $filename = "{$slug}.{$ext}";
                                            $counter = 1;
                                            while ($storage->exists(($dir ? $dir.'/' : '').$filename)) {
                                                $filename = "{$slug}-{$counter}.{$ext}";
                                                $counter++;
                                            }

                                            return $filename;
                                        }),

                                    Forms\Components\Placeholder::make('media_preview')
                                        ->label('Anteprima Attuale')
                                        ->visible(fn (string $operation): bool => $operation === 'edit')
                                        ->content(function (?MediaItem $record): HtmlString {
                                            if (! $record || ! $record->file_path) {
                                                return new HtmlString('<span class="text-gray-400">Nessun file presente</span>');
                                            }

                                            $url = e($record->url);
                                            if (str_starts_with($record->mime_type ?? '', 'video/')) {
                                                return new HtmlString(
                                                    '<video src="'.$url.'" controls class="max-h-72 w-full rounded-lg border border-gray-200 dark:border-gray-800 bg-black"></video>'
                                                );
                                            }

                                            return new HtmlString(
                                                '<div class="flex items-center justify-center p-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800"><img src="'.$url.'" alt="'.e($record->alt_text ?? $record->title).'" class="max-h-72 rounded-md object-contain shadow-sm" /></div>'
                                            );
                                        }),
                                ]),
                        ])->columnSpan(['lg' => 2]),

                        Forms\Components\Group::make([
                            Forms\Components\Section::make('Metadati')
                                ->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->label('Titolo')
                                        ->maxLength(255)
                                        ->helperText('Se lasciato vuoto, verrà utilizzato il nome originale del file senza estensione.')
                                        ->placeholder('Es. Locandina United Gospel 2026'),

                                    Forms\Components\TextInput::make('alt_text')
                                        ->label('Testo Alternativo (Alt Text)')
                                        ->maxLength(255)
                                        ->helperText('Descrizione per accessibilità e SEO.'),

                                    Forms\Components\Textarea::make('caption')
                                        ->label('Didascalia')
                                        ->rows(3),

                                    Forms\Components\Select::make('collection')
                                        ->label('Collezione / Categoria')
                                        ->options([
                                            'uploads' => 'Caricamenti Diretti (Uploads)',
                                            'featured' => 'Locandine (Featured)',
                                            'gallery' => 'Galleria Slider',
                                            'wall' => 'Wall dei Ricordi',
                                            'general' => 'Generale',
                                        ])
                                        ->default('uploads')
                                        ->required(),
                                ]),

                            Forms\Components\Section::make('Dettagli Tecnici')
                                ->visible(fn (string $operation): bool => $operation === 'edit')
                                ->schema([
                                    Forms\Components\Placeholder::make('formatted_size')
                                        ->label('Dimensione File')
                                        ->content(fn (?MediaItem $record): string => $record?->formatted_size ?? '—'),

                                    Forms\Components\Placeholder::make('dimensions')
                                        ->label('Risoluzione')
                                        ->content(fn (?MediaItem $record): string => ($record?->width && $record?->height) ? "{$record->width} × {$record->height} px" : '—'),

                                    Forms\Components\Placeholder::make('mime_type')
                                        ->label('Tipo MIME')
                                        ->content(fn (?MediaItem $record): string => $record?->mime_type ?? '—'),

                                    Forms\Components\TextInput::make('public_url')
                                        ->label('Link URL Pubblico')
                                        ->formatStateUsing(fn (?MediaItem $record): string => $record?->url ?? '')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->suffixAction(
                                            Forms\Components\Actions\Action::make('copy_url')
                                                ->icon('heroicon-o-clipboard-document')
                                                ->tooltip('Copia Link')
                                                ->action(function (?MediaItem $record): void {
                                                    if ($record) {
                                                        Notification::make()
                                                            ->title('Link copiato negli appunti')
                                                            ->body($record->url)
                                                            ->success()
                                                            ->send();
                                                    }
                                                })
                                        ),
                                ]),
                        ])->columnSpan(['lg' => 1]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid(function ($livewire): ?array {
                if (method_exists($livewire, 'isGridView') && ! $livewire->isGridView()) {
                    return null;
                }

                return [
                    'default' => 2,
                    'sm' => 2,
                    'md' => 3,
                    'lg' => 4,
                    'xl' => 5,
                    '2xl' => 6,
                ];
            })
            ->columns([
                Tables\Columns\ImageColumn::make('file_path')
                    ->label('Anteprima')
                    ->disk('public')
                    ->extraImgAttributes(fn ($livewire): array => [
                        'class' => (method_exists($livewire, 'isGridView') && $livewire->isGridView())
                            ? 'w-full h-44 object-cover rounded-t-xl transition duration-200 hover:scale-105'
                            : 'rounded-lg object-cover shadow-sm',
                    ]),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titolo & File')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(24)
                    ->description(fn (MediaItem $record, $livewire): string => (method_exists($livewire, 'isGridView') && $livewire->isGridView())
                        ? "{$record->formatted_size} • {$record->collection}"
                        : $record->file_name
                    )
                    ->wrap(),

                Tables\Columns\TextColumn::make('collection')
                    ->label('Collezione')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'featured' => 'Locandine',
                        'gallery' => 'Galleria',
                        'wall' => 'Wall Ricordi',
                        'uploads' => 'Uploads',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'featured',
                        'info' => 'gallery',
                        'warning' => 'wall',
                        'success' => 'uploads',
                        'gray' => 'general',
                    ])
                    ->sortable()
                    ->hidden(fn ($livewire): bool => method_exists($livewire, 'isGridView') && $livewire->isGridView()),

                Tables\Columns\TextColumn::make('mime_type')
                    ->label('MIME Type')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->hidden(fn ($livewire): bool => method_exists($livewire, 'isGridView') && $livewire->isGridView()),

                Tables\Columns\TextColumn::make('formatted_size')
                    ->label('Dimensione')
                    ->badge()
                    ->color('info')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('file_size', $direction))
                    ->hidden(fn ($livewire): bool => method_exists($livewire, 'isGridView') && $livewire->isGridView()),

                Tables\Columns\TextColumn::make('resolution')
                    ->label('Risoluzione')
                    ->state(fn (MediaItem $record): string => ($record->width && $record->height) ? "{$record->width} × {$record->height}" : '—')
                    ->alignCenter()
                    ->hidden(fn ($livewire): bool => method_exists($livewire, 'isGridView') && $livewire->isGridView()),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Caricato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->hidden(fn ($livewire): bool => method_exists($livewire, 'isGridView') && $livewire->isGridView()),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordAction('view')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo Contenuto')
                    ->options([
                        'image' => 'Immagini',
                        'video' => 'Video',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'image' => $query->where('mime_type', 'like', 'image/%'),
                            'video' => $query->where('mime_type', 'like', 'video/%'),
                            default => $query,
                        };
                    }),

                Tables\Filters\SelectFilter::make('collection')
                    ->label('Collezione')
                    ->options([
                        'featured' => 'Locandine (Featured)',
                        'gallery' => 'Galleria Slider',
                        'wall' => 'Wall dei Ricordi',
                        'uploads' => 'Caricamenti Diretti',
                        'general' => 'Generale',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make('view')
                    ->label('Dettagli')
                    ->modalHeading(fn (MediaItem $record): string => $record->title ?: $record->file_name)
                    ->slideOver()
                    ->infolist([
                        Infolists\Components\Section::make('Anteprima')
                            ->schema([
                                Infolists\Components\ImageEntry::make('file_path')
                                    ->label('')
                                    ->disk('public')
                                    ->extraImgAttributes(['class' => 'max-h-80 w-auto mx-auto rounded-lg shadow-sm object-contain']),
                            ]),
                        Infolists\Components\Section::make('Informazioni File')
                            ->columns(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('title')->label('Titolo'),
                                Infolists\Components\TextEntry::make('original_name')->label('Nome Originale'),
                                Infolists\Components\TextEntry::make('collection')->label('Collezione')->badge(),
                                Infolists\Components\TextEntry::make('formatted_size')->label('Dimensione File')->badge()->color('info'),
                                Infolists\Components\TextEntry::make('dimensions')->label('Risoluzione')->state(fn (MediaItem $record): string => ($record->width && $record->height) ? "{$record->width} × {$record->height} px" : '—'),
                                Infolists\Components\TextEntry::make('mime_type')->label('MIME Type')->badge()->color('gray'),
                                Infolists\Components\TextEntry::make('created_at')->label('Data Caricamento')->dateTime('d/m/Y H:i'),
                                Infolists\Components\TextEntry::make('url')->label('URL Pubblico')->copyable()->copyMessage('URL copiato negli appunti!'),
                            ]),
                    ])
                    ->extraModalFooterActions([
                        Tables\Actions\Action::make('edit_media')
                            ->label('Modifica Metadati')
                            ->icon('heroicon-o-pencil-square')
                            ->url(fn (MediaItem $record): string => MediaResource::getUrl('edit', ['record' => $record])),
                    ]),
                Tables\Actions\Action::make('copy_url')
                    ->label('Copia Link')
                    ->icon('heroicon-o-clipboard-document')
                    ->color('gray')
                    ->action(function (MediaItem $record): void {
                        Notification::make()
                            ->title('Link copiato negli appunti')
                            ->body($record->url)
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Elimina'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Elimina Selezionati'),
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
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
