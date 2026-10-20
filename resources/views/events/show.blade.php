<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
            <span class="font-bold uppercase tracking-wider">Modalità Anteprima Scheda Evento</span>
            <span class="text-emerald-200 hidden sm:inline">• Layout: {{ $effectiveLayout === 'layout1' ? 'Galleria' : 'Locandina' }}</span>
        </div>
        <div class="flex items-center gap-3">
            <a href="/admin/events/{{ $event->id }}/edit" class="bg-white/20 hover:bg-white/30 text-white px-2.5 py-1 rounded font-semibold transition">
                ✏️ Modifica
            </a>
            <a href="/admin/events/{{ $event->id }}/bookings" class="bg-white/20 hover:bg-white/30 text-white px-2.5 py-1 rounded font-semibold transition">
                🎟️ Prenotazioni
            </a>
        </div>
    </div>

    <!-- Header Istituzionale DFN -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-20 flex justify-between items-center">
            <a href="/" class="flex items-center gap-3.5">
                <div class="w-11 h-11 bg-[#004b23] rounded-xl flex items-center justify-center text-white font-black text-xl shadow-sm">
                    🏛️
                </div>
                <div class="flex flex-col">
                    <span class="font-extrabold text-lg tracking-tight text-[#004b23]">DFN Prenotazioni</span>
                    <span class="text-xs text-slate-500 font-medium">Portale Eventi & Prenotazioni Culturali</span>
                </div>
            </a>
            <nav class="hidden md:flex items-center gap-6 text-sm font-semibold text-slate-600">
                <a href="/" class="hover:text-[#004b23] transition">Prossimi eventi</a>
                <a href="#info" class="hover:text-[#004b23] transition">Convenzioni FAI</a>
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
            <!-- LAYOUT 1 — Galleria Slider in Cima -->
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

        <!-- Griglia Contenuto: Locandina a sinistra + Scheda e Prenotazione a destra -->
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

            <!-- Colonna Destra: Dettagli, Logistica & Form Funnel Prenotazione -->
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
                            <p>Un evento straordinario di cultura, bellezza e valorizzazione promosso dal FAI.</p>
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
                            ⏰ {{ $event->event_time_start ? substr($event->event_time_start, 0, 5) : '10:00' }}
                            @if($event->event_time_end)
                                - {{ substr($event->event_time_end, 0, 5) }}
                            @endif
                        </span>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-xs font-semibold text-emerald-800">
                        <div class="flex items-center gap-2">
                            <span class="pulsing-dot"></span>
                            <span>Prenotazioni attive</span>
                        </div>
                        <span class="text-slate-500">
                            {{ $event->access_type === 'time_slots' ? 'Accesso con fasce orarie' : 'Accesso a flusso continuo' }}
                        </span>
                    </div>
                </div>

                <!-- ============================================================= -->
                <!-- FUNNEL PRENOTAZIONE INTERATTIVO                               -->
                <!-- ============================================================= -->
                <div id="booking-widget" class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200 shadow-md">

                    @if($errors->any())
                        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm space-y-1">
                            <div class="font-bold flex items-center gap-1.5 text-rose-800">
                                <span>⚠️</span> Si prega di verificare i seguenti campi:
                            </div>
                            <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="booking-form" action="{{ route('event.book', $event) }}" method="POST">
                        @csrf

                        <!-- Hidden Inputs for Quantities -->
                        <input type="hidden" name="persons_standard" id="input_standard" value="{{ old('persons_standard', 1) }}">
                        <input type="hidden" name="persons_fai" id="input_fai" value="{{ old('persons_fai', 0) }}">

                        <!-- ================= STEP 1: PARTECIPANTI ================= -->
                        <div class="mb-6">
                            <div class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                                <span>🎟️</span> 1. Seleziona Partecipanti
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
                                        <button type="button" onclick="changeQty('standard', -1)" class="w-8 h-8 rounded bg-slate-700 hover:bg-slate-600 text-white font-bold flex items-center justify-center transition cursor-pointer">−</button>
                                        <span id="display_standard" class="font-extrabold text-base px-3">{{ old('persons_standard', 1) }}</span>
                                        <button type="button" onclick="changeQty('standard', 1)" class="w-8 h-8 rounded bg-slate-700 hover:bg-slate-600 text-white font-bold flex items-center justify-center transition cursor-pointer">+</button>
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
                                        <button type="button" onclick="changeQty('fai', -1)" class="w-8 h-8 rounded bg-white/20 hover:bg-white/30 text-white font-bold flex items-center justify-center transition cursor-pointer">−</button>
                                        <span id="display_fai" class="font-extrabold text-base px-3">{{ old('persons_fai', 0) }}</span>
                                        <button type="button" onclick="changeQty('fai', 1)" class="w-8 h-8 rounded bg-white/20 hover:bg-white/30 text-white font-bold flex items-center justify-center transition cursor-pointer">+</button>
                                    </div>
                                    <span class="text-[11px] text-center uppercase tracking-wider text-orange-100 font-bold">Ridotto</span>
                                </div>
                            </div>

                            <!-- Barra Totale e Subtotale -->
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 flex justify-between items-center text-xs">
                                <span class="text-slate-600">
                                    Partecipanti totali: <strong id="total_persons_count" class="text-slate-900">1</strong>
                                </span>
                                <div class="text-right">
                                    <span class="text-slate-500 mr-1.5">Contributo totale:</span>
                                    <strong id="total_price_display" class="text-base text-[#004b23] font-black">
                                        €{{ number_format($event->price_standard, 2) }}
                                    </strong>
                                </div>
                            </div>
                        </div>

                        <!-- ================= STEP 2: SCELTA SLOT (SE FASCE ORARIE) ================= -->
                        @if($event->access_type === 'time_slots')
                            <div class="mb-6 pt-5 border-t border-slate-100">
                                <div class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                    <span>⏰</span> 2. Seleziona Orario di Visita (Turno)
                                </div>

                                @if($event->slots && $event->slots->count() > 0)
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-60 overflow-y-auto pr-1">
                                        @foreach($event->slots as $s)
                                            @php
                                                $freeSeats = $s->availableCapacity();
                                                $isFull = $freeSeats <= 0;
                                            @endphp
                                            <label class="slot-option relative flex items-center justify-between p-3 rounded-xl border {{ $isFull ? 'bg-slate-100 border-slate-200 opacity-60 cursor-not-allowed' : 'border-slate-200 hover:border-[#004b23] bg-white cursor-pointer has-[:checked]:border-[#004b23] has-[:checked]:bg-emerald-50/50' }} transition">
                                                <div class="flex items-center gap-2.5">
                                                    <input type="radio" 
                                                           name="slot_id" 
                                                           value="{{ $s->id }}" 
                                                           data-available="{{ $freeSeats }}"
                                                           {{ old('slot_id') == $s->id ? 'checked' : '' }}
                                                           {{ $isFull ? 'disabled' : '' }}
                                                           class="w-4 h-4 text-[#004b23] focus:ring-[#004b23]">
                                                    <div>
                                                        <span class="font-extrabold text-xs sm:text-sm text-slate-900 block">
                                                            {{ substr($s->start_time, 0, 5) }} - {{ substr($s->end_time, 0, 5) }}
                                                        </span>
                                                        <span class="text-[11px] text-slate-500">
                                                            {{ $s->slot_date->format('d/m/Y') }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    @if($isFull)
                                                        <span class="text-[10px] font-bold uppercase tracking-wider bg-slate-200 text-slate-600 px-2 py-0.5 rounded">Esaurito</span>
                                                    @elseif($freeSeats <= 5)
                                                        <span class="text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 px-2 py-0.5 rounded">Ultimi {{ $freeSeats }} posti</span>
                                                    @else
                                                        <span class="text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded">{{ $freeSeats }} liberi</span>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                                        Nessuna fascia oraria disponibile al momento per questo evento.
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- ================= STEP 3: DATI INTESTATARIO ================= -->
                        <div class="mb-6 pt-5 border-t border-slate-100">
                            <div class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <span>👤</span> {{ $event->access_type === 'time_slots' ? '3' : '2' }}. Dati Intestatario Prenotazione
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 mb-1">Nome *</label>
                                    <input type="text" name="first_name" value="{{ old('first_name') }}" required
                                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#004b23] focus:ring-1 focus:ring-[#004b23] outline-hidden">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 mb-1">Cognome *</label>
                                    <input type="text" name="last_name" value="{{ old('last_name') }}" required
                                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#004b23] focus:ring-1 focus:ring-[#004b23] outline-hidden">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 mb-1">Email per Conferma *</label>
                                    <input type="email" name="email" value="{{ old('email') }}" required
                                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#004b23] focus:ring-1 focus:ring-[#004b23] outline-hidden">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 mb-1">Telefono / Cellulare *</label>
                                    <input type="tel" name="phone" value="{{ old('phone') }}" required
                                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#004b23] focus:ring-1 focus:ring-[#004b23] outline-hidden">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Note o richieste particolari (facoltativo)</label>
                                <textarea name="notes" rows="2" class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 px-3 py-2 focus:border-[#004b23] focus:ring-1 focus:ring-[#004b23] outline-hidden">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <!-- ================= STEP 4: TESSERE SOCI FAI (DINAMICO) ================= -->
                        <div id="fai-cards-container" class="mb-6 pt-5 border-t border-slate-100 {{ old('persons_fai', 0) > 0 ? '' : 'hidden' }}">
                            <div class="text-xs font-bold text-[#e74f30] uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <span>🛡️</span> Tessere Soci FAI
                            </div>
                            <p class="text-xs text-slate-500 mb-3">
                                Inserisci il numero di tessera per ciascun partecipante a tariffa ridotta FAI per verificarne la validità.
                            </p>

                            <div id="fai-card-inputs" class="space-y-2.5">
                                <!-- Generati dinamicamente via JS -->
                            </div>
                        </div>

                        <!-- ================= METODO DI PAGAMENTO ================= -->
                        <div class="mb-6 pt-5 border-t border-slate-100">
                            <div class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <span>💳</span> Metodo di Partecipazione
                            </div>
                            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input type="radio" name="payment_method" value="in_loco" checked class="w-4 h-4 text-[#004b23] focus:ring-[#004b23]">
                                    <div>
                                        <span class="text-xs sm:text-sm font-extrabold text-slate-900 block">Pagamento / Offerta in Loco</span>
                                        <span class="text-[11px] text-slate-500">Versa il contributo all'ingresso (contanti o POS)</span>
                                    </div>
                                </label>
                                <span class="text-xs font-bold text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-lg">Attivo</span>
                            </div>
                        </div>

                        <!-- Bottone Principale Conferma -->
                        <button type="submit" id="submit-booking-btn" class="w-full bg-[#e74f30] hover:bg-[#cf3d20] text-white font-bold py-3.5 px-6 rounded-xl text-base shadow-sm hover:shadow transition flex items-center justify-center gap-2 cursor-pointer">
                            <span>Conferma e Ricevi Biglietto</span>
                            <span>→</span>
                        </button>
                    </form>
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

    <!-- Script Reattività Funnel Prenotazione & Convalida FAI -->
    <script>
        const PRICE_STANDARD = {{ (float) $event->price_standard }};
        const PRICE_FAI = {{ (float) ($event->price_fai ?? $event->price_standard) }};

        let qtyStandard = parseInt(document.getElementById('input_standard').value) || 1;
        let qtyFai = parseInt(document.getElementById('input_fai').value) || 0;

        function updateTotals() {
            const totalPersons = qtyStandard + qtyFai;
            const totalPrice = (qtyStandard * PRICE_STANDARD) + (qtyFai * PRICE_FAI);

            document.getElementById('display_standard').textContent = qtyStandard;
            document.getElementById('display_fai').textContent = qtyFai;
            document.getElementById('input_standard').value = qtyStandard;
            document.getElementById('input_fai').value = qtyFai;

            document.getElementById('total_persons_count').textContent = totalPersons;
            document.getElementById('total_price_display').textContent = '€' + totalPrice.toFixed(2);

            // Container Tessere FAI
            const faiContainer = document.getElementById('fai-cards-container');
            const faiInputsContainer = document.getElementById('fai-card-inputs');

            if (qtyFai > 0) {
                faiContainer.classList.remove('hidden');
                
                // Mantiene valori digitati precedentemente
                const existingValues = [];
                faiInputsContainer.querySelectorAll('input').forEach(input => {
                    existingValues.push(input.value);
                });

                faiInputsContainer.innerHTML = '';
                for (let i = 0; i < qtyFai; i++) {
                    const val = existingValues[i] || '';
                    const wrapper = document.createElement('div');
                    wrapper.className = 'flex flex-col sm:flex-row items-stretch sm:items-center gap-2 p-2.5 bg-slate-50 rounded-xl border border-slate-200';
                    wrapper.innerHTML = `
                        <div class="flex-1 flex items-center gap-2">
                            <span class="text-xs font-bold text-[#e74f30]">Socio #${i + 1}:</span>
                            <input type="text" name="fai_cards[]" value="${val}" placeholder="Es. 123456" 
                                   class="fai-card-input flex-1 text-xs sm:text-sm font-mono px-3 py-1.5 rounded-lg border border-slate-300 focus:border-[#004b23] focus:ring-1 focus:ring-[#004b23] outline-hidden"
                                   data-index="${i}">
                        </div>
                        <button type="button" onclick="checkFaiCard(${i})" class="cursor-pointer bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                            🔍 Verifica
                        </button>
                        <span id="fai_card_status_${i}" class="text-xs font-semibold text-slate-500 min-w-32"></span>
                    `;
                    faiInputsContainer.appendChild(wrapper);
                }
            } else {
                faiContainer.classList.add('hidden');
                faiInputsContainer.innerHTML = '';
            }

            // Disabilita turni con posti insufficienti per totalPersons
            document.querySelectorAll('input[name="slot_id"]').forEach(radio => {
                const available = parseInt(radio.dataset.available) || 0;
                const label = radio.closest('.slot-option');
                if (available < totalPersons) {
                    radio.disabled = true;
                    if (radio.checked) {
                        radio.checked = false;
                    }
                    if (label) {
                        label.classList.add('opacity-40', 'cursor-not-allowed');
                    }
                } else {
                    radio.disabled = false;
                    if (label) {
                        label.classList.remove('opacity-40', 'cursor-not-allowed');
                    }
                }
            });
        }

        function changeQty(type, delta) {
            if (type === 'standard') {
                qtyStandard = Math.max(0, qtyStandard + delta);
            } else if (type === 'fai') {
                qtyFai = Math.max(0, qtyFai + delta);
            }

            // Almeno 1 partecipante in totale
            if (qtyStandard + qtyFai === 0) {
                if (type === 'standard') qtyStandard = 1;
                else qtyFai = 1;
            }

            updateTotals();
        }

        async function checkFaiCard(index) {
            const input = document.querySelector(`input[data-index="${index}"]`);
            const statusEl = document.getElementById(`fai_card_status_${index}`);
            if (!input || !statusEl) return;

            const cardNumber = input.value.trim();
            if (!cardNumber) {
                statusEl.textContent = 'Inserire il numero';
                statusEl.className = 'text-xs font-semibold text-amber-600';
                return;
            }

            statusEl.textContent = 'Verifica in corso...';
            statusEl.className = 'text-xs font-semibold text-slate-400';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch('/api/check-fai-card', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ card_number: cardNumber })
                });

                const data = await response.json();
                if (data.valid) {
                    statusEl.textContent = `✔ Valida: ${data.member_name}`;
                    statusEl.className = 'text-xs font-bold text-emerald-700';
                } else {
                    statusEl.textContent = '⚠️ ' + data.message;
                    statusEl.className = 'text-xs font-semibold text-amber-600';
                }
            } catch (err) {
                statusEl.textContent = '⚠️ Servizio non disponibile';
                statusEl.className = 'text-xs font-semibold text-amber-600';
            }
        }

        // Inizializza al caricamento
        document.addEventListener('DOMContentLoaded', () => {
            updateTotals();
        });
    </script>

</body>
</html>
