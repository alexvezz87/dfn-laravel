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
    class="group relative aspect-square w-full overflow-hidden rounded-lg border border-gray-200 bg-gray-100 shadow-xs transition duration-200 hover:border-primary-500 hover:shadow-lg dark:border-white/10 dark:bg-gray-800 cursor-pointer"
>
    @if ($isImage)
        <img 
            src="{{ $record->url }}" 
            alt="{{ $record->alt_text ?? $record->title }}" 
            class="h-full w-full object-cover transition duration-200 group-hover:scale-105" 
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
    <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-150">
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white/95 text-gray-800 shadow-xl backdrop-blur-xs transition transform group-hover:scale-110">
            <x-heroicon-m-pencil-square class="h-6 w-6 text-gray-900" />
        </div>
    </div>
</div>
