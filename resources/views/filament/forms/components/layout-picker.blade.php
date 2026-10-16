<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }" class="flex flex-col gap-2.5 pt-1">
        
        <!-- Automatico -->
        <label 
            @click="state = 'auto'"
            :class="state === 'auto' ? 'border-[#004b23] bg-[#f0fdf4] shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300'"
            class="flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all duration-200"
        >
            <input 
                type="radio" 
                name="{{ $getStatePath() }}" 
                value="auto" 
                x-model="state" 
                class="text-[#004b23] focus:ring-[#004b23] w-4 h-4 shrink-0"
            >
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <!-- Mini preview scheletro Auto -->
                <div class="flex items-center gap-1 shrink-0">
                    <div class="w-7 h-5 bg-slate-300 rounded p-0.5 flex flex-col gap-0.5">
                        <div class="h-2 bg-slate-400 rounded-xs"></div>
                        <div class="flex gap-0.5 h-1.5">
                            <div class="flex-1 bg-slate-400 rounded-xs"></div>
                            <div class="flex-1 bg-slate-400 rounded-xs"></div>
                        </div>
                    </div>
                    <span class="text-xs text-slate-400 font-bold">/</span>
                    <div class="w-7 h-5 bg-slate-300 rounded p-0.5 flex gap-0.5">
                        <div class="w-2.5 bg-slate-400 rounded-xs"></div>
                        <div class="flex-1 flex flex-col gap-0.5">
                            <div class="h-1.5 bg-slate-400 rounded-xs"></div>
                            <div class="h-1.5 bg-slate-400 rounded-xs"></div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col min-w-0">
                    <span class="font-bold text-xs text-slate-900 leading-tight">Automatico</span>
                    <span class="text-[11px] text-slate-500 leading-tight">Layout 2 se no galleria, Layout 1 se galleria</span>
                </div>
            </div>
        </label>

        <!-- Layout 1 — Galleria -->
        <label 
            @click="state = 'layout1'"
            :class="state === 'layout1' ? 'border-[#004b23] bg-[#f0fdf4] shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300'"
            class="flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all duration-200"
        >
            <input 
                type="radio" 
                name="{{ $getStatePath() }}" 
                value="layout1" 
                x-model="state" 
                class="text-[#004b23] focus:ring-[#004b23] w-4 h-4 shrink-0"
            >
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <!-- Mini preview Galleria -->
                <div class="w-8 h-6 bg-slate-300 rounded p-0.5 flex flex-col gap-0.5 shrink-0">
                    <div class="h-2.5 bg-slate-400 rounded-xs"></div>
                    <div class="flex gap-0.5 h-2">
                        <div class="flex-1 bg-slate-400 rounded-xs"></div>
                        <div class="flex-1 bg-slate-400 rounded-xs"></div>
                    </div>
                </div>

                <div class="flex flex-col min-w-0">
                    <span class="font-bold text-xs text-slate-900 leading-tight">Layout 1 — Galleria</span>
                    <span class="text-[11px] text-slate-500 leading-tight">Slider/Galleria in cima + Info e Form in basso</span>
                </div>
            </div>
        </label>

        <!-- Layout 2 — Locandina -->
        <label 
            @click="state = 'layout2'"
            :class="state === 'layout2' ? 'border-[#004b23] bg-[#f0fdf4] shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300'"
            class="flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all duration-200"
        >
            <input 
                type="radio" 
                name="{{ $getStatePath() }}" 
                value="layout2" 
                x-model="state" 
                class="text-[#004b23] focus:ring-[#004b23] w-4 h-4 shrink-0"
            >
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <!-- Mini preview Locandina -->
                <div class="w-8 h-6 bg-slate-300 rounded p-0.5 flex gap-0.5 shrink-0">
                    <div class="w-2.5 bg-slate-400 rounded-xs"></div>
                    <div class="flex-1 flex flex-col gap-0.5">
                        <div class="h-1 bg-slate-400 rounded-xs"></div>
                        <div class="h-1 bg-slate-400 rounded-xs"></div>
                        <div class="h-2 bg-[#e74f30] rounded-xs"></div>
                    </div>
                </div>

                <div class="flex flex-col min-w-0">
                    <span class="font-bold text-xs text-slate-900 leading-tight">Layout 2 — Locandina</span>
                    <span class="text-[11px] text-slate-500 leading-tight">Locandina verticale a sinistra + Form a destra</span>
                </div>
            </div>
        </label>

    </div>
</x-dynamic-component>
