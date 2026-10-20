<?php

namespace App\Filament\Resources\EventResource\Pages\Concerns;

use App\Actions\OptimizeImageAction;
use App\Models\Setting;
use Filament\Notifications\Notification;

trait OptimizesEventImages
{
    /**
     * Ottimizza i file multimediali presenti nei dati del form prima della persistenza a database.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function optimizeMediaBeforeSave(array $data): array
    {
        if (! Setting::getBool('media_auto_compress', true)) {
            return $data;
        }

        $action = new OptimizeImageAction;
        $optimizedAny = false;
        $totalOriginal = 0;
        $totalFinal = 0;

        // 1. Ottimizzazione della locandina principale (featured_image)
        if (! empty($data['featured_image'])) {
            if (is_string($data['featured_image'])) {
                $newPath = $action->execute($data['featured_image'], 'public');
                if ($action->wasOptimized()) {
                    $optimizedAny = true;
                    $totalOriginal += $action->getOriginalBytes();
                    $totalFinal += $action->getFinalBytes();
                }
                $data['featured_image'] = $newPath;
            } elseif (is_array($data['featured_image'])) {
                foreach ($data['featured_image'] as $key => $imagePath) {
                    if (is_string($imagePath)) {
                        $newPath = $action->execute($imagePath, 'public');
                        if ($action->wasOptimized()) {
                            $optimizedAny = true;
                            $totalOriginal += $action->getOriginalBytes();
                            $totalFinal += $action->getFinalBytes();
                        }
                        $data['featured_image'][$key] = $newPath;
                    }
                }
            }
        }

        // 2. Ottimizzazione delle foto slider della galleria (gallery_images)
        if (! empty($data['gallery_images']) && is_array($data['gallery_images'])) {
            $optimizedGallery = [];
            foreach ($data['gallery_images'] as $imagePath) {
                if (is_string($imagePath)) {
                    $newGalleryPath = $action->execute($imagePath, 'public');
                    if ($action->wasOptimized()) {
                        $optimizedAny = true;
                        $totalOriginal += $action->getOriginalBytes();
                        $totalFinal += $action->getFinalBytes();
                    }
                    $optimizedGallery[] = $newGalleryPath;
                } else {
                    $optimizedGallery[] = $imagePath;
                }
            }
            $data['gallery_images'] = $optimizedGallery;
        }

        if ($optimizedAny) {
            $savingsSummary = '';
            if ($totalOriginal > $totalFinal && $totalFinal > 0) {
                $saved = $totalOriginal - $totalFinal;
                $percent = (int) round(($saved / $totalOriginal) * 100);
                $savingsSummary = sprintf(
                    ' (da %s a %s, -%d%%)',
                    $action->formatBytes($totalOriginal),
                    $action->formatBytes($totalFinal),
                    $percent
                );
            }

            Notification::make()
                ->info()
                ->title('Immagine ottimizzata automaticamente')
                ->body('Il file è stato compresso per garantire massime prestazioni su web e mobile'.$savingsSummary.'.')
                ->send();
        }

        return $data;
    }

    /**
     * Verifica e ottimizza eventuali immagini sul modello dopo il salvataggio.
     */
    protected function optimizeRecordMedia(): void
    {
        if (! Setting::getBool('media_auto_compress', true)) {
            return;
        }

        $record = method_exists($this, 'getRecord') ? $this->getRecord() : null;
        if (! $record) {
            return;
        }

        $action = new OptimizeImageAction;
        $dirty = false;

        if (! empty($record->featured_image) && is_string($record->featured_image)) {
            $optimized = $action->execute($record->featured_image, 'public');
            if ($optimized !== $record->featured_image) {
                $record->featured_image = $optimized;
                $dirty = true;
            }
        }

        if (! empty($record->gallery_images) && is_array($record->gallery_images)) {
            $optimizedGallery = [];
            foreach ($record->gallery_images as $img) {
                if (is_string($img)) {
                    $opt = $action->execute($img, 'public');
                    if ($opt !== $img) {
                        $dirty = true;
                    }
                    $optimizedGallery[] = $opt;
                } else {
                    $optimizedGallery[] = $img;
                }
            }
            if ($dirty) {
                $record->gallery_images = $optimizedGallery;
            }
        }

        if ($dirty) {
            $record->saveQuietly();
        }
    }
}
