<div class="p-4 sm:p-6">
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-7 2xl:grid-cols-8">
        @foreach ($records as $record)
            @include('filament.tables.media-grid-card', ['record' => $record])
        @endforeach
    </div>
</div>
