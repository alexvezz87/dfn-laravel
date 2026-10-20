<?php

namespace App\Actions;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class OptimizeImageAction
{
    protected int $originalBytes = 0;

    protected int $finalBytes = 0;

    /**
     * Esegue l'ottimizzazione e il ridimensionamento di un'immagine presente sullo storage.
     *
     * @param  string  $path  Percorso relativo del file sullo storage
     * @param  string  $disk  Disco di storage (default: 'public')
     * @return string Percorso finale del file (eventualmente convertito in WebP)
     */
    public function execute(string $path, string $disk = 'public'): string
    {
        $this->originalBytes = 0;
        $this->finalBytes = 0;

        if (! Setting::getBool('media_auto_compress', true)) {
            return $path;
        }

        $storage = Storage::disk($disk);
        if (! $storage->exists($path)) {
            return $path;
        }

        if (! extension_loaded('gd')) {
            return $path;
        }

        $fileContents = $storage->get($path);
        if (! $fileContents) {
            return $path;
        }

        $this->originalBytes = strlen($fileContents);
        $this->finalBytes = $this->originalBytes;

        $image = @imagecreatefromstring($fileContents);
        if ($image === false) {
            return $path;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $maxDimension = Setting::getInt('media_max_dimension_px', 1920);
        $quality = Setting::getInt('media_quality_percent', 82);
        $convertToWebp = Setting::getBool('media_convert_webp', true);
        $targetBytes = Setting::getInt('media_max_image_mb', 8) * 1024 * 1024;

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $needsResize = ($width > $maxDimension || $height > $maxDimension);
        $needsWebp = ($convertToWebp && $extension !== 'webp');
        $needsRecompression = ($this->originalBytes > $targetBytes);

        // Se l'immagine è già sotto soglia, non supera le dimensioni massime e non richiede WebP, mantienila invariata
        if (! $needsResize && ! $needsWebp && ! $needsRecompression) {
            imagedestroy($image);

            return $path;
        }

        // Se l'immagine supera la risoluzione massima, ridimensionala mantenendo le proporzioni
        if ($needsResize) {
            if ($width >= $height) {
                $newWidth = $maxDimension;
                $newHeight = (int) round(($height / $width) * $maxDimension);
            } else {
                $newHeight = $maxDimension;
                $newWidth = (int) round(($width / $height) * $maxDimension);
            }

            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

            // Preserva la trasparenza per PNG e WebP
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
            $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
            imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);

            imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resizedImage;
        }

        $destinationPath = $path;
        if ($convertToWebp) {
            $pathInfo = pathinfo($path);
            $dir = ($pathInfo['dirname'] !== '.' && $pathInfo['dirname'] !== '') ? $pathInfo['dirname'].'/' : '';
            $destinationPath = $dir.$pathInfo['filename'].'.webp';
        }

        ob_start();
        if ($convertToWebp) {
            imagewebp($image, null, $quality);
        } else {
            imagejpeg($image, null, $quality);
        }
        $outputData = ob_get_clean();
        imagedestroy($image);

        if ($outputData !== false && strlen($outputData) > 0) {
            $this->finalBytes = strlen($outputData);

            if ($destinationPath !== $path) {
                $storage->delete($path);
            }
            $storage->put($destinationPath, $outputData);

            return $destinationPath;
        }

        return $path;
    }

    /**
     * Restituisce i byte prima dell'ottimizzazione.
     */
    public function getOriginalBytes(): int
    {
        return $this->originalBytes;
    }

    /**
     * Restituisce i byte dopo l'ottimizzazione.
     */
    public function getFinalBytes(): int
    {
        return $this->finalBytes;
    }

    /**
     * Indica se il file è stato effettivamente modificato/compresso.
     */
    public function wasOptimized(): bool
    {
        return $this->originalBytes > 0 && $this->finalBytes > 0 && ($this->finalBytes !== $this->originalBytes);
    }

    /**
     * Formatta i byte in formato leggibile (MB / KB).
     */
    public function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }

        return number_format($bytes / 1024, 0).' KB';
    }

    /**
     * Restituisce un riepilogo testuale dell'ottimizzazione (es: 'da 4.2 MB a 450 KB (-89%)').
     */
    public function getSavingsSummary(): ?string
    {
        if (! $this->wasOptimized()) {
            return null;
        }

        $saved = $this->originalBytes - $this->finalBytes;
        if ($saved <= 0) {
            return null;
        }

        $percent = (int) round(($saved / $this->originalBytes) * 100);

        return sprintf(
            'da %s a %s (-%d%%)',
            $this->formatBytes($this->originalBytes),
            $this->formatBytes($this->finalBytes),
            $percent
        );
    }

    /**
     * Metodo helper statico per l'esecuzione immediata dell'azione.
     */
    public static function run(string $path, string $disk = 'public'): string
    {
        return (new static)->execute($path, $disk);
    }
}
