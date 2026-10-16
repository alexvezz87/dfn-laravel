<div class="space-y-4 text-sm text-slate-700 py-1">
    @if(!empty($intro))
        <p class="leading-relaxed">{!! $intro !!}</p>
    @endif

    @if(!empty($items))
        <ul class="list-disc pl-5 space-y-2">
            @foreach($items as $item)
                <li class="leading-relaxed">{!! $item !!}</li>
            @endforeach
        </ul>
    @endif

    @if(!empty($tip))
        <div class="p-3.5 bg-emerald-50 border-l-4 border-[#004b23] rounded-r-md text-emerald-950 text-xs leading-relaxed">
            <strong class="font-bold text-[#004b23]">{{ $tipTitle ?? 'Suggerimento:' }}</strong> {!! $tip !!}
        </div>
    @endif

    @if(!empty($warn))
        <div class="p-3.5 bg-amber-50 border-l-4 border-amber-500 rounded-r-md text-amber-950 text-xs leading-relaxed">
            <strong class="font-bold text-amber-800">⚠️ Attenzione:</strong> {!! $warn !!}
        </div>
    @endif
</div>
