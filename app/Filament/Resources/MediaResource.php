<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\MediaItem;
use Filament\Forms;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Form;
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
                                        ->required(fn (string $operation): bool => $operation === 'create')
                                        ->visible(fn (string $operation): bool => $operation === 'create')
                                        ->helperText('Carica immagini (JPG, PNG, WebP) o video (MP4, WebM) fino a 50 MB.')
                                        ->getUploadedFileNameForStorageUsing(function (BaseFileUpload $component, TemporaryUploadedFile $file): string {
                                            $originalBase = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                                            $slug = Str::slug($originalBase) ?: 'media';
                                            $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
                                            $dir = $component->getDirectory();
                                            $prefix = $dir ? $dir.'/' : '';
                                            $diskName = $component->getDiskName();
                                            /** @var FilesystemAdapter $storage */
                                            $storage = Storage::disk($diskName);

                                            $counter = 0;
                                            do {
                                                $candidateBase = $counter === 0 ? $slug : "{$slug}-{$counter}";
                                                $filename = "{$candidateBase}.{$ext}";
                                                $webpFilename = "{$candidateBase}.webp";
                                                $exists = $storage->exists($prefix.$filename) || $storage->exists($prefix.$webpFilename);
                                                $counter++;
                                            } while ($exists);

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
                                                    '<video src="'.$url.'" controls class="max-h-[460px] w-full rounded-lg border border-gray-200 dark:border-gray-800 bg-black"></video>'
                                                );
                                            }

                                            return new HtmlString(
                                                '<div class="flex items-center justify-center p-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800"><img src="'.$url.'" alt="'.e($record->alt_text ?? $record->title).'" class="max-h-[460px] w-auto mx-auto rounded-md object-contain shadow-sm" /></div>'
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
            ->content(function ($livewire) {
                if (method_exists($livewire, 'isGridView') && $livewire->isGridView()) {
                    return view('filament.tables.media-grid');
                }

                return null;
            })
            ->columns([
                Tables\Columns\ImageColumn::make('file_path')
                    ->label('Anteprima')
                    ->disk('public')
                    ->size(40)
                    ->square()
                    ->extraImgAttributes(['class' => 'rounded object-cover shadow-xs']),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titolo')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(40)
                    ->tooltip(fn (MediaItem $record): string => $record->file_name),

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
                    ->sortable(),

                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Tipo MIME')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('formatted_size')
                    ->label('Dimensione')
                    ->badge()
                    ->color('info')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('file_size', $direction)),

                Tables\Columns\TextColumn::make('resolution')
                    ->label('Risoluzione')
                    ->state(fn (MediaItem $record): string => ($record->width && $record->height) ? "{$record->width} × {$record->height}" : '—')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordAction('edit')
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
                Tables\Actions\EditAction::make('edit')
                    ->label('Modifica')
                    ->modalWidth('5xl')
                    ->modalHeading(fn (MediaItem $record): string => 'Dettagli Allegato: '.($record->title ?: $record->file_name))
                    ->icon('heroicon-m-pencil-square')
                    ->iconButton()
                    ->tooltip('Dettagli e Modifica'),

                Tables\Actions\Action::make('copy_url')
                    ->label('Copia Link')
                    ->icon('heroicon-o-clipboard-document')
                    ->iconButton()
                    ->color('gray')
                    ->tooltip('Copia Link URL')
                    ->action(function (MediaItem $record): void {
                        Notification::make()
                            ->title('Link copiato negli appunti')
                            ->body($record->url)
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make('delete')
                    ->label('Elimina')
                    ->icon('heroicon-o-trash')
                    ->iconButton()
                    ->tooltip('Elimina'),
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
