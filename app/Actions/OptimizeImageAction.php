<?php

namespace App\Actions;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class OptimizeImageAction
{
    /**
     * Esegue l'ottimizzazione e il ridimensionamento di un'immagine presente sullo storage.
     *
     * @param  string  $path  Percorso relativo del file sullo storage
     * @param  string  $disk  Disco di storage (default: 'public')
     * @return string Percorso finale del file (eventualmente convertito in WebP)
     */
    public function execute(string $path, string $disk = 'public'): string
    {
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

        $image = @imagecreatefromstring($fileContents);
        if ($image === false) {
            return $path;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $maxDimension = Setting::getInt('media_max_dimension_px', 1920);
        $quality = Setting::getInt('media_quality_percent', 82);
        $convertToWebp = Setting::getBool('media_convert_webp', true);

        // Se l'immagine supera la risoluzione massima, ridimensionala mantenendo le proporzioni
        if ($width > $maxDimension || $height > $maxDimension) {
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
            if ($destinationPath !== $path) {
                $storage->delete($path);
            }
            $storage->put($destinationPath, $outputData);

            return $destinationPath;
        }

        return $path;
    }

    /**
     * Metodo helper statico per l'esecuzione immediata dell'azione.
     */
    public static function run(string $path, string $disk = 'public'): string
    {
        return (new static)->execute($path, $disk);
    }
}
