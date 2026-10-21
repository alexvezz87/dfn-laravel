<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class MediaItem extends Model
{
    use HasFactory;

    protected $table = 'media_items';

    protected $fillable = [
        'disk',
        'file_path',
        'file_name',
        'original_name',
        'mime_type',
        'file_size',
        'width',
        'height',
        'collection',
        'title',
        'alt_text',
        'caption',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (self $item): void {
            if ($item->file_path && Storage::disk($item->disk)->exists($item->file_path)) {
                Storage::disk($item->disk)->delete($item->file_path);
            }
        });
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (float) $this->file_size;
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);

        return round($bytes / pow(1024, $i), 1).' '.$units[$i];
    }

    public function getUrlAttribute(): string
    {
        /** @var FilesystemAdapter $storage */
        $storage = Storage::disk($this->disk);

        return $storage->url($this->file_path);
    }

    public static function createFromStoragePath(string $path, string $collection = 'general', ?string $originalName = null, string $disk = 'public'): ?self
    {
        $path = ltrim($path, '/');

        $existing = static::query()
            ->where('disk', $disk)
            ->where('file_path', $path)
            ->first();

        if ($existing) {
            return $existing;
        }

        /** @var FilesystemAdapter $storage */
        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            return null;
        }

        $fileName = basename($path);
        $fileSize = $storage->size($path) ?: 0;
        $mimeType = $storage->mimeType($path);
        $width = null;
        $height = null;

        try {
            $fullPath = $storage->path($path);
            if (file_exists($fullPath)) {
                $imageInfo = @getimagesize($fullPath);
                if (is_array($imageInfo)) {
                    $width = (int) ($imageInfo[0] ?? null);
                    $height = (int) ($imageInfo[1] ?? null);
                    if (! empty($imageInfo['mime'])) {
                        $mimeType = $imageInfo['mime'];
                    }
                }
            }
        } catch (\Throwable) {
            // Ignora se getimagesize non supportato sul driver o stream
        }

        $title = pathinfo($originalName ?? $fileName, PATHINFO_FILENAME);

        return static::create([
            'disk' => $disk,
            'file_path' => $path,
            'file_name' => $fileName,
            'original_name' => $originalName ?? $fileName,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'width' => $width,
            'height' => $height,
            'collection' => $collection,
            'title' => $title,
        ]);
    }
}
