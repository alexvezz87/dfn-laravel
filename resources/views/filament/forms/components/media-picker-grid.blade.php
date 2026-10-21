<div 
    x-data="{
        selected: @entangle($getStatePath()),
        isMultiple: {{ $isMultiple ? 'true' : 'false' }},
        search: '',
        toggleSelect(path) {
            if (this.isMultiple) {
                if (!Array.isArray(this.selected)) {
                    this.selected = this.selected ? [this.selected] : [];
                }
                const idx = this.selected.indexOf(path);
                if (idx > -1) {
                    this.selected.splice(idx, 1);
                } else {
                    this.selected.push(path);
                }
            } else {
                this.selected = (this.selected === path) ? null : path;
            }
        },
        isSelected(path) {
            if (this.isMultiple) {
                return Array.isArray(this.selected) && this.selected.includes(path);
            }
            return this.selected === path;
        }
    }"
    class="space-y-4"
>
    <!-- Barra Ricerca -->
    <div class="relative">
        <input 
            type="text" 
            x-model="search" 
            placeholder="Cerca nella libreria media per titolo o nome file..." 
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 pl-9 text-xs focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800"
        />
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
            <x-heroicon-m-magnifying-glass class="h-4 w-4" />
        </div>
    </div>

    <!-- Griglia Tessere a Dimensione Fissa (repeat auto-fill minmax 130px) -->
    <div 
        class="dfn-picker-grid max-h-[420px] overflow-y-auto p-1"
        style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px; width: 100%;"
    >
        @foreach ($mediaItems as $item)
            @php
                $isImage = str_starts_with($item->mime_type ?? '', 'image/');
                $isVideo = str_starts_with($item->mime_type ?? '', 'video/');
                $searchTerms = strtolower(addslashes(($item->title ?? '') . ' ' . ($item->file_name ?? '')));
            @endphp
            <div 
                x-show="!search || '{{ $searchTerms }}'.includes(search.toLowerCase())"
                x-on:click="toggleSelect('{{ $item->file_path }}')"
                class="group relative aspect-square cursor-pointer overflow-hidden rounded-lg border-2 transition duration-150 select-none bg-gray-50 dark:bg-gray-900"
                :class="isSelected('{{ $item->file_path }}') ? 'border-primary-600 ring-2 ring-primary-500/40' : 'border-gray-200 hover:border-gray-400 dark:border-white/10'"
                style="aspect-ratio: 1 / 1; width: 100%; max-width: 150px;"
            >
                @if ($isImage)
                    <img 
                        src="{{ $item->url }}" 
                        alt="{{ $item->alt_text ?? $item->title }}" 
                        class="h-full w-full object-cover" 
                        loading="lazy" 
                    />
                @elseif ($isVideo)
                    <div class="flex h-full w-full flex-col items-center justify-center p-2 text-gray-400">
                        <x-heroicon-o-video-camera class="h-8 w-8 text-gray-400" />
                        <span class="mt-1 text-[10px] uppercase font-semibold">Video</span>
                    </div>
                @else
                    <div class="flex h-full w-full flex-col items-center justify-center p-2 text-gray-400">
                        <x-heroicon-o-document-text class="h-8 w-8 text-gray-400" />
                        <span class="mt-1 text-[10px] uppercase font-semibold">File</span>
                    </div>
                @endif

                <!-- Checkmark Selezione -->
                <div 
                    x-show="isSelected('{{ $item->file_path }}')"
                    class="absolute inset-0 flex items-center justify-center bg-primary-900/30 backdrop-blur-[1px]"
                >
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-600 text-white shadow-md">
                        <x-heroicon-m-check class="h-5 w-5 stroke-2" />
                    </div>
                </div>

                <!-- Titolo Inferiore -->
                <div class="absolute inset-x-0 bottom-0 bg-black/60 px-1 py-0.5 text-center pointer-events-none">
                    <p class="truncate text-[10px] font-medium text-white">
                        {{ $item->title ?: $item->file_name }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Riepilogo Inferiore -->
    <div class="flex items-center justify-between border-t border-gray-200 pt-2 text-xs text-gray-500 dark:border-white/10">
        <template x-if="isMultiple">
            <span class="font-medium text-gray-700 dark:text-gray-300" x-text="Array.isArray(selected) && selected.length ? selected.length + ' file selezionati' : 'Nessun file selezionato'"></span>
        </template>
        <template x-if="!isMultiple">
            <span class="font-medium text-gray-700 dark:text-gray-300" x-text="selected ? '1 file selezionato' : 'Fai clic su un file per selezionarlo'"></span>
        </template>
    </div>
</div>
