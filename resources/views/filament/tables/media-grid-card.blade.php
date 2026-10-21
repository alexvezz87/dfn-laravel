@php
    $record = $getRecord();
    $isImage = str_starts_with($record->mime_type ?? '', 'image/');
    $isVideo = str_starts_with($record->mime_type ?? '', 'video/');
@endphp

<div class="group relative aspect-square w-full overflow-hidden rounded-md border border-gray-200 bg-gray-50 shadow-xs transition duration-150 hover:border-primary-500 hover:shadow-md hover:ring-2 hover:ring-primary-500/20 dark:border-white/10 dark:bg-white/5 cursor-pointer">
    @if ($isImage)
        <img 
            src="{{ $record->url }}" 
            alt="{{ $record->alt_text ?? $record->title }}" 
            class="h-full w-full object-cover transition duration-200 group-hover:scale-105" 
            loading="lazy" 
        />
    @elseif ($isVideo)
        <div class="flex h-full w-full flex-col items-center justify-center p-3 text-gray-400">
            <x-heroicon-o-video-camera class="h-10 w-10 text-gray-400 dark:text-gray-500" />
            <span class="mt-1 text-[10px] text-gray-500 uppercase font-semibold">Video</span>
        </div>
    @else
        <div class="flex h-full w-full flex-col items-center justify-center p-3 text-gray-400">
            <x-heroicon-o-document-text class="h-10 w-10 text-gray-400 dark:text-gray-500" />
            <span class="mt-1 text-[10px] text-gray-500 uppercase font-semibold">File</span>
        </div>
    @endif

    {{-- Didascalia / Titolo in sovrimpressione inferiore stile WordPress --}}
    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 via-black/50 to-transparent p-1.5 pt-3">
        <p class="truncate text-center text-[11px] font-medium text-white select-none">
            {{ $record->title ?: $record->file_name }}
        </p>
    </div>
</div>
