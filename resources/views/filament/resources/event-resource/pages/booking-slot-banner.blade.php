@php
    $bookedCount = (int) $record->bookings()->where('status', 'confirmed')->count();
    $occupiedSeats = (int) $record->bookings()->where('status', 'confirmed')->sum('total_persons');
    $capacity = (int) ($record->total_capacity ?: $record->slots()->sum('max_capacity') ?: 0);
    $percent = $capacity > 0 ? min(100, round(($occupiedSeats / $capacity) * 100)) : 0;
    $timeStart = $record->event_time_start ? substr($record->event_time_start, 0, 5) : '00:00';
    $timeEnd = $record->event_time_end ? substr($record->event_time_end, 0, 5) : 'FINE';
    $accessLabel = match($record->access_type) {
        'free_flow' => 'Flusso Libero',
        'time_slots' => 'Fasce Orarie',
        default => ucfirst($record->access_type ?? 'Standard')
    };
    $styleWidth = "width: {$percent}%;";
@endphp

<div class="mb-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="flex flex-wrap items-center justify-between gap-3 pb-2">
        <div class="flex items-center gap-2">
            <x-filament::icon icon="heroicon-m-queue-list" class="h-5 w-5 text-primary-500" />
            <span class="font-bold text-gray-900 dark:text-gray-100">{{ $accessLabel }} — {{ $timeStart }} → {{ $timeEnd }}</span>
            <span class="rounded-full bg-primary-50 px-2.5 py-0.5 text-xs font-semibold text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                {{ $percent }}% occupato
            </span>
        </div>
        <div class="text-sm font-medium text-gray-600 dark:text-gray-400">
            <span class="font-bold text-gray-900 dark:text-gray-100">{{ $occupiedSeats }}</span> / {{ $capacity > 0 ? $capacity : 'Illimitati' }} posti • <span class="font-bold text-gray-900 dark:text-gray-100">{{ $bookedCount }}</span> prenotazioni
        </div>
    </div>
    
    @if($capacity > 0)
    <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
        <div class="h-full rounded-full transition-all duration-500 {{ $percent >= 90 ? 'bg-amber-500' : 'bg-emerald-500' }}" style="{{ $styleWidth }}"></div>
    </div>
    @endif
</div>
