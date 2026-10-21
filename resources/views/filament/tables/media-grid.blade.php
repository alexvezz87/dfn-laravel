@php
    $columns = \App\Models\Setting::getInt('media_grid_columns', 7);
@endphp

<div class="p-4 sm:p-6">
    <div 
        class="dfn-media-grid"
        style="--dfn-grid-cols: {{ $columns }};"
    >
        @foreach ($records as $record)
            @include('filament.tables.media-grid-card', ['record' => $record])
        @endforeach
    </div>
</div>
