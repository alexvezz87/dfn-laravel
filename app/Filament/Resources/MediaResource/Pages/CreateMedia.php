<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    public function getTitle(): string
    {
        return 'Carica Nuovo File Multimediale';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $path = $data['file_path'] ?? '';
        if (is_array($path)) {
            $path = reset($path) ?: '';
        }

        $path = ltrim((string) $path, '/');
        $disk = 'public';

        $data['disk'] = $disk;
        $data['file_path'] = $path;
        $data['file_name'] = basename($path);
        $data['original_name'] = $data['original_name'] ?? basename($path);
        $data['collection'] = $data['collection'] ?? 'uploads';

        if (empty($data['title'])) {
            $data['title'] = pathinfo($data['original_name'] ?? basename($path), PATHINFO_FILENAME);
        }

        /** @var FilesystemAdapter $storage */
        $storage = Storage::disk($disk);

        if ($path !== '' && $storage->exists($path)) {
            $data['file_size'] = $storage->size($path) ?: 0;
            $data['mime_type'] = $storage->mimeType($path);

            try {
                $fullPath = $storage->path($path);
                if (file_exists($fullPath)) {
                    $imageInfo = @getimagesize($fullPath);
                    if (is_array($imageInfo)) {
                        $data['width'] = (int) ($imageInfo[0] ?? null);
                        $data['height'] = (int) ($imageInfo[1] ?? null);
                        if (! empty($imageInfo['mime'])) {
                            $data['mime_type'] = $imageInfo['mime'];
                        }
                    }
                }
            } catch (\Throwable) {
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Torna alla Libreria')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(MediaResource::getUrl('index')),
        ];
    }

    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Carica e Salva')
            ->icon('heroicon-o-arrow-up-tray');
    }
}
