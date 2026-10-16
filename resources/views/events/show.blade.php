<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->title }} — DFN Prenotazioni</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        :root {
            --dfn-primary: #004b23;
            --dfn-primary-dark: #002f16;
            --dfn-accent: #e74f30;
            --dfn-accent-hover: #cf3d20;
            --dfn-gold: #c69c3a;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .pulsing-dot {
            width: 8px;
            height: 8px;
            background-color: #16a34a;
            border-radius: 9999px;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.7);
            animation: pulse 1.8s infinite cubic-bezier(0.66, 0, 0, 1);
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.7); }
            70% { box-shadow: 0 0 0 8px rgba(22, 163, 74, 0); }
            100% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0); }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased">

    <!-- Top Admin Preview Bar -->
    <div class="bg-[#004b23] text-white text-xs py-2 px-4 shadow-sm flex justify-between items-center z-50">
        <div class="flex items-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400"></span>
            <span class="font-bold uppercase tracking-wider">Modalità Anteprima Scheda Evento</span>
            <span class="text-emerald-200 hidden sm:inline">• Layout attivo: {{ $effectiveLayout === 'layout1' ? 'Layout 1 (Galleria)' : 'Layout 2 (Locandina)' }}</span>
        </div>
        <div class="flex items-center gap-3">
            <a href="/admin/events/{{ $event->id }}/edit" class="bg-white/20 hover:bg-white/30 text-white px-2.5 py-1 rounded font-semibold transition">
                ✏️ Torna alla Modifica
            </a>
            <a href="/admin/events/{{ $event->id }}/bookings" class="bg-white/20 hover:bg-white/30 text-white px-2.5 py-1 rounded font-semibold transition">
                🎟️ Prenotazioni
            </a>
        </div>
    </div>

    <!-- Header Istituzionale DFN -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-20 flex justify-between items-center">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 bg-[#004b23] rounded-xl flex items-center justify-center text-white font-black text-xl shadow-sm">
                    🏛️
                </div>
                <div class="flex flex-col">
                    <span class="font-extrabold text-lg tracking-tight text-[#004b23]">DFN Prenotazioni</span>
                    <span class="text-xs text-slate-500 font-medium">Portale Eventi & Prenotazioni Culturali</span>
                </div>
            </div>
            <nav class="hidden md:flex items-center gap-6 text-sm font-semibold text-slate-600">
                <a href="/" class="hover:text-[#004b23] transition">Prossimi eventi</a>
                <a href="#info" class="hover:text-[#004b23] transition">Convenzioni</a>
                <a href="/admin" class="hover:text-[#004b23] transition flex items-center gap-1.5 bg-slate-100 px-3 py-1.5 rounded-lg">
                    <span>👤</span> Area Riservata
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 py-8">

        <!-- Titolo Principale Evento -->
        <div class="mb-6 flex items-center gap-3 text-slate-900">
            <span class="text-2xl text-[#004b23]">📅</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#004b23]">
                {{ $event->title }}
            </h1>
        </div>

        @if($effectiveLayout === 'layout1')
            <!-- ========================================== -->
            <!-- LAYOUT 1 — Galleria Slider in Cima        -->
            <!-- ========================================== -->
            @if(!empty($event->gallery_images) && count($event->gallery_images) > 0)
                <div class="mb-8 rounded-2xl overflow-hidden shadow-md bg-slate-900 border border-slate-200">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 p-2">
                        @foreach($event->gallery_images as $img)
                            <div class="h-64 rounded-xl overflow-hidden bg-slate-800">
                                <img src="{{ asset('storage/' . $img) }}" alt="Foto evento" class="w-full h-full object-cover hover:scale-105 transition duration-300">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        <!-- Griglia Contenuto: Locandina a sinistra (se Layout 2) + Scheda a destra -->
        <div class="grid grid-cols-1 {{ $effectiveLayout === 'layout2' ? 'lg:grid-cols-12' : 'lg:grid-cols-1' }} gap-8 items-start">

            @if($effectiveLayout === 'layout2')
                <!-- Colonna Sinistra: Locandina Verticale -->
                <div class="lg:col-span-5 w-full">
                    <div class="bg-white rounded-2xl p-2.5 shadow-md border border-slate-200 sticky top-28">
                        @if($event->featured_image)
                            <img src="{{ asset('storage/' . $event->featured_image) }}" 
                                 alt="{{ $event->title }}" 
                                 class="w-full rounded-xl object-cover shadow-xs">
                        @else
                            <div class="w-full aspect-3/4 rounded-xl bg-gradient-to-br from-[#004b23] to-[#002f16] flex flex-col items-center justify-center p-6 text-white text-center shadow-xs">
                                <span class="text-5xl mb-4">🎼</span>
                                <h3 class="text-xl font-bold tracking-tight mb-2">{{ $event->title }}</h3>
                                <p class="text-xs text-emerald-200">Locandina Ufficiale dell'Evento</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Colonna Destra: Dettagli, Logistica & Form Partecipanti -->
            <div class="{{ $effectiveLayout === 'layout2' ? 'lg:col-span-7' : 'w-full max-w-4xl mx-auto' }} space-y-6">

                <!-- Blocco Descrizione -->
                <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200 border-l-4 border-l-[#004b23] shadow-xs">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <span>🏰</span> Descrizione
                    </div>
                    <div class="prose max-w-none text-slate-700 text-sm sm:text-base leading-relaxed">
                        @if($event->description)
                            {!! $event->description !!}
                        @else
                            <p>Un evento straordinario di musica, condivisione e bellezza artistica promosso dal territorio.</p>
                        @endif
                    </div>
                </div>

                <!-- Blocco Luogo ed Orario -->
                <div class="bg-slate-50 p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                        <span>📍</span> Luogo ed Orario
                    </div>
                    <div class="text-base sm:text-lg font-bold text-[#004b23]">
                        {{ $event->location ?? 'Sede da definire' }}{{ $event->city ? ' • ' . $event->city : '' }}
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs sm:text-sm text-slate-600 font-medium">
                        <span class="flex items-center gap-1">
                            📅 {{ $event->event_date_start ? $event->event_date_start->format('d/m/Y') : 'Data non impostata' }}
                            @if($event->event_date_end && $event->event_date_end->ne($event->event_date_start))
                                - {{ $event->event_date_end->format('d/m/Y') }}
                            @endif
                        </span>
                        <span class="text-slate-300">|</span>
                        <span class="flex items-center gap-1">
                            ⏰ {{ $event->event_time_start ? substr($event->event_time_start, 0, 5) : '21:00' }}
                            @if($event->event_time_end)
                                - {{ substr($event->event_time_end, 0, 5) }}
                            @endif
                        </span>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center gap-2 text-xs font-semibold text-emerald-800">
                        <span class="pulsing-dot"></span>
                        <span>Posti disponibili</span>
                        @if($event->total_capacity > 0)
                            <span class="text-slate-400 font-normal">({{ $event->total_capacity }} capienza massima)</span>
                        @endif
                    </div>
                </div>

                <!-- Box Selettore Partecipanti (Replica Fedele del Widget) -->
                <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200 shadow-md">
                    <div class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                        <span>🎟️</span> Seleziona Partecipanti
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <!-- Card Non Iscritto (Intero) -->
                        <div class="bg-slate-800 text-white rounded-xl p-4 flex flex-col justify-between shadow-xs">
                            <div class="flex justify-between items-start mb-3">
                                <span class="text-xs font-bold tracking-wide uppercase text-slate-300">Non Iscritto</span>
                                <div class="text-right">
                                    <span class="text-lg font-black tracking-tight">€{{ number_format($event->price_standard, 2) }}</span>
                                    <span class="text-[11px] text-slate-300 block">/ cad.</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between bg-slate-900/60 rounded-lg p-1.5 mb-2">
                                <button type="button" class="w-8 h-8 rounded bg-slate-700 hover:bg-slate-600 text-white font-bold flex items-center justify-center transition">−</button>
                                <span class="font-extrabold text-base px-3">1</span>
                                <button type="button" class="w-8 h-8 rounded bg-slate-700 hover:bg-slate-600 text-white font-bold flex items-center justify-center transition">+</button>
                            </div>
                            <span class="text-[11px] text-center uppercase tracking-wider text-slate-400 font-bold">Intero</span>
                        </div>

                        <!-- Card Socio FAI (Ridotto) -->
                        <div class="bg-[#e74f30] text-white rounded-xl p-4 flex flex-col justify-between shadow-xs">
                            <div class="flex justify-between items-start mb-3">
                                <span class="text-xs font-bold tracking-wide uppercase text-orange-100">Socio FAI</span>
                                <div class="text-right">
                                    <span class="text-lg font-black tracking-tight">€{{ number_format($event->price_fai ?? $event->price_standard, 2) }}</span>
                                    <span class="text-[11px] text-orange-100 block">/ cad.</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between bg-black/20 rounded-lg p-1.5 mb-2">
                                <button type="button" class="w-8 h-8 rounded bg-white/20 hover:bg-white/30 text-white font-bold flex items-center justify-center transition">−</button>
                                <span class="font-extrabold text-base px-3">0</span>
                                <button type="button" class="w-8 h-8 rounded bg-white/20 hover:bg-white/30 text-white font-bold flex items-center justify-center transition">+</button>
                            </div>
                            <span class="text-[11px] text-center uppercase tracking-wider text-orange-100 font-bold">Ridotto</span>
                        </div>
                    </div>

                    <!-- Avviso Socio FAI -->
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs text-slate-600 leading-relaxed mb-6">
                        ℹ️ <strong>La tariffa Socio FAI</strong> è riservata ai soli possessori di tessera FAI in corso di validità. Il numero di tessera verrà richiesto al passaggio successivo.
                    </div>

                    <!-- Bottone Principale Continua -->
                    <button type="button" class="w-full bg-[#e74f30] hover:bg-[#cf3d20] text-white font-bold py-3.5 px-6 rounded-xl text-base shadow-sm hover:shadow transition flex items-center justify-center gap-2 cursor-pointer">
                        <span>Continua</span>
                        <span>→</span>
                    </button>
                </div>

                @if(!empty($event->wall_media) && count($event->wall_media) > 0)
                    <!-- Wall dei Ricordi (Post-Evento) -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
                        <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                            <span>📸🎬</span> Wall dei Ricordi
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($event->wall_media as $media)
                                <div class="h-40 rounded-xl overflow-hidden bg-slate-100 border border-slate-200">
                                    @if(str_ends_with(strtolower($media), '.mp4') || str_ends_with(strtolower($media), '.webm'))
                                        <video src="{{ asset('storage/' . $media) }}" controls class="w-full h-full object-cover"></video>
                                    @else
                                        <img src="{{ asset('storage/' . $media) }}" alt="Ricordo evento" class="w-full h-full object-cover">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

        </div>

        <!-- Footer Ritorno -->
        <div class="mt-12 text-center border-t border-slate-200 pt-8">
            <a href="/admin/events" class="inline-flex items-center gap-2 text-sm font-bold text-[#004b23] bg-emerald-50 hover:bg-emerald-100 px-5 py-2.5 rounded-xl transition">
                <span>←</span>
                <span>Torna al Tabellone Eventi</span>
            </a>
        </div>

    </main>

    <!-- Footer Istituzionale -->
    <footer class="bg-slate-900 text-white mt-16 py-10 px-4 text-xs text-slate-400 border-t border-slate-800">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <p class="font-bold text-white text-sm">DFN Prenotazioni</p>
                <p>Portale per la prenotazione di eventi culturali sul territorio novarese.</p>
            </div>
            <div class="text-right">
                <p>© {{ date('Y') }} DFN Prenotazioni. Tutti i diritti riservati.</p>
            </div>
        </div>
    </footer>

</body>
</html>
