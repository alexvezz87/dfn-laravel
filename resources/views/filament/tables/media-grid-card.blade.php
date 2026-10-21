@php
    $record = $record ?? (isset($getRecord) ? $getRecord() : null);
    if (! $record) {
        return;
    }
    $isImage = str_starts_with($record->mime_type ?? '', 'image/');
    $isVideo = str_starts_with($record->mime_type ?? '', 'video/');
@endphp

<div 
    wire:click="mountTableAction('edit', '{{ $record->getKey() }}')"
    class="dfn-media-card group"
>
    @if ($isImage)
        <img 
            src="{{ $record->url }}" 
            alt="{{ $record->alt_text ?? $record->title }}" 
            loading="lazy" 
        />
    @elseif ($isVideo)
        <div class="flex h-full w-full flex-col items-center justify-center p-3 text-gray-400">
            <x-heroicon-o-video-camera class="h-12 w-12 text-gray-400 dark:text-gray-500" />
            <span class="mt-2 text-xs text-gray-500 uppercase font-semibold">Video</span>
        </div>
    @else
        <div class="flex h-full w-full flex-col items-center justify-center p-3 text-gray-400">
            <x-heroicon-o-document-text class="h-12 w-12 text-gray-400 dark:text-gray-500" />
            <span class="mt-2 text-xs text-gray-500 uppercase font-semibold">File</span>
        </div>
    @endif

    {{-- Overlay in hover: compare ESCLUSIVAMENTE l'icona matita per la modifica --}}
    <div class="dfn-media-overlay">
        <div class="dfn-media-pencil-btn">
            <x-heroicon-m-pencil-square class="h-6 w-6 text-gray-900" />
        </div>
    </div>
</div>
