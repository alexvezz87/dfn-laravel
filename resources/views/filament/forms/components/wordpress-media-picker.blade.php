@php
    $state = $getState();
    $isMultiple = $isMultiple();
@endphp

<div 
    x-data="{
        state: $wire.entangle('{{ $getStatePath() }}'),
        removeSingle() {
            this.state = null;
        },
        removeMultiple(index) {
            if (Array.isArray(this.state)) {
                this.state.splice(index, 1);
            }
        }
    }"
    class="w-full space-y-3"
>
    {{-- CASO 1: SINGOLA IMMAGINE (Locandina Principale) --}}
    @if (! $isMultiple)
        <template x-if="!state">
            <div class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-300 dark:border-white/10 rounded-xl bg-gray-50/50 dark:bg-white/5 hover:border-primary-500 transition">
                <button 
                    type="button"
                    wire:click="mountFormComponentAction('{{ $getStatePath() }}', 'openPicker')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-white/20 text-sm font-semibold text-gray-700 dark:text-gray-200 shadow-xs hover:bg-gray-50 dark:hover:bg-white/5 hover:text-primary-600 transition"
                >
                    <x-heroicon-m-photo class="w-5 h-5 text-gray-500" />
                    <span>Imposta immagine in evidenza</span>
                </button>
                <p class="text-xs text-gray-400 mt-2">Nessuna locandina impostata</p>
            </div>
        </template>

        <template x-if="state">
            <div class="relative group border border-gray-200 dark:border-white/10 rounded-xl overflow-hidden bg-white dark:bg-gray-900 p-2 shadow-xs max-w-xs">
                <div class="aspect-4/3 w-full overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
                    <img 
                        :src="'/storage/' + String(state).replace(/^\/?storage\//, '')" 
                        class="w-full h-full object-cover" 
                    />
                </div>
                <div class="flex items-center justify-between pt-2 px-1 text-xs">
                    <button 
                        type="button"
                        wire:click="mountFormComponentAction('{{ $getStatePath() }}', 'openPicker')"
                        class="font-medium text-primary-600 hover:text-primary-700 hover:underline inline-flex items-center gap-1"
                    >
                        <x-heroicon-m-arrow-path class="w-3.5 h-3.5" />
                        <span>Sostituisci</span>
                    </button>
                    <button 
                        type="button"
                        x-on:click="removeSingle()"
                        class="font-medium text-danger-600 hover:text-danger-700 hover:underline inline-flex items-center gap-1"
                    >
                        <x-heroicon-m-trash class="w-3.5 h-3.5" />
                        <span>Rimuovi</span>
                    </button>
                </div>
            </div>
        </template>
    @else
        {{-- CASO 2: GALLERIA MULTIPLA (Slider e Wall) --}}
        <div class="space-y-3">
            <template x-if="Array.isArray(state) && state.length">
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                    <template x-for="(item, index) in state" :key="index">
                        <div class="group relative aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-white/10 bg-gray-100 shadow-xs">
                            <img :src="'/storage/' + String(item).replace(/^\/?storage\//, '')" class="w-full h-full object-cover" />
                            <button 
                                type="button"
                                x-on:click="removeMultiple(index)"
                                class="absolute top-1.5 right-1.5 p-1 rounded-full bg-black/60 text-white hover:bg-danger-600 transition"
                                title="Rimuovi dalla galleria"
                            >
                                <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    </template>
                </div>
            </template>

            <div>
                <button 
                    type="button"
                    wire:click="mountFormComponentAction('{{ $getStatePath() }}', 'openPicker')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-white/20 text-xs font-semibold text-gray-700 dark:text-gray-200 shadow-xs hover:bg-gray-50 dark:hover:bg-white/5 hover:text-primary-600 transition"
                >
                    <x-heroicon-m-plus class="w-4 h-4 text-primary-600" />
                    <span>Aggiungi immagini alla galleria</span>
                </button>
            </div>
        </div>
    @endif
</div>
