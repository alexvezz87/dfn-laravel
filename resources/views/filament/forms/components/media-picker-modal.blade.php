<div 
    x-data="{
        selected: @entangle($getStatePath()),
        multiple: {{ $multiple ? 'true' : 'false' }},
        search: '',
        toggleSelect(path) {
            if (this.multiple) {
                if (!Array.isArray(this.selected)) {
                    this.selected = this.selected ? [this.selected] : [];
                }
                const index = this.selected.indexOf(path);
                if (index > -1) {
                    this.selected.splice(index, 1);
                } else {
                    this.selected.push(path);
                }
            } else {
                this.selected = (this.selected === path) ? null : path;
            }
        },
        isSelected(path) {
            if (this.multiple) {
                return Array.isArray(this.selected) && this.selected.includes(path);
            }
            return this.selected === path;
        }
    }"
    class="space-y-4"
>
    <!-- Barra di Ricerca Live -->
    <div class="relative">
        <input 
            type="text" 
            x-model="search" 
            placeholder="Cerca nella libreria per titolo o nome file..." 
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 pl-10 text-sm shadow-xs focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-white"
        />
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
            <x-heroicon-m-magnifying-glass class="h-5 w-5" />
        </div>
    </div>

    @if ($mediaItems->isEmpty())
        <div class="flex flex-col items-center justify-center py-12 text-center text-gray-500">
            <x-heroicon-o-photo class="h-12 w-12 text-gray-300 dark:text-gray-600" />
            <p class="mt-2 text-sm font-medium">Nessun file presente nella Libreria Media.</p>
            <p class="text-xs text-gray-400">Carica i tuoi primi file dalla sezione Media prima di selezionarli.</p>
        </div>
    @else
        <!-- Griglia Media Picker -->
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3 max-h-[460px] overflow-y-auto p-1">
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
                    :class="isSelected('{{ $item->file_path }}') ? 'border-primary-600 ring-2 ring-primary-500/40' : 'border-gray-200 hover:border-primary-400 dark:border-white/10'"
                >
                    @if ($isImage)
                        <img 
                            src="{{ $item->url }}" 
                            alt="{{ $item->alt_text ?? $item->title }}" 
                            class="h-full w-full object-cover transition duration-150 group-hover:scale-105" 
                            loading="lazy" 
                        />
                    @elseif ($isVideo)
                        <div class="flex h-full w-full flex-col items-center justify-center p-2 text-gray-400">
                            <x-heroicon-o-video-camera class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                            <span class="mt-1 text-[10px] text-gray-500 uppercase font-semibold">Video</span>
                        </div>
                    @else
                        <div class="flex h-full w-full flex-col items-center justify-center p-2 text-gray-400">
                            <x-heroicon-o-document-text class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                            <span class="mt-1 text-[10px] text-gray-500 uppercase font-semibold">File</span>
                        </div>
                    @endif

                    <!-- Overlay di Selezione con Spunta Verde -->
                    <div 
                        x-show="isSelected('{{ $item->file_path }}')"
                        class="absolute inset-0 flex items-center justify-center bg-primary-900/30 backdrop-blur-xs"
                    >
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-600 text-white shadow-lg">
                            <x-heroicon-m-check class="h-5 w-5 stroke-2" />
                        </div>
                    </div>

                    <!-- Etichetta Titolo in Basso -->
                    <div class="absolute inset-x-0 bottom-0 bg-black/60 px-1 py-0.5 text-center pointer-events-none">
                        <p class="truncate text-[10px] font-medium text-white">
                            {{ $item->title ?: $item->file_name }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Barra di Riepilogo Selezione -->
        <div class="flex items-center justify-between border-t border-gray-200 pt-3 text-xs text-gray-500 dark:border-white/10">
            <template x-if="multiple">
                <span class="font-medium text-gray-700 dark:text-gray-300" x-text="Array.isArray(selected) && selected.length ? selected.length + ' file selezionati' : 'Nessun file selezionato'"></span>
            </template>
            <template x-if="!multiple">
                <span class="font-medium text-gray-700 dark:text-gray-300" x-text="selected ? '1 immagine selezionata' : 'Fai clic su un\'immagine per selezionarla'"></span>
            </template>
            <span class="text-gray-400 italic">Puoi sempre caricare un nuovo file dal tuo computer trascinandolo nel box sottostante.</span>
        </div>
    @endif
</div>
