<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biglietto Prenotazione {{ $booking->booking_code }} — {{ $event->title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        :root {
            --dfn-primary: #004b23;
            --dfn-primary-dark: #002f16;
            --dfn-accent: #e74f30;
            --dfn-gold: #c69c3a;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-shadow-none { box-shadow: none !important; border-color: #cbd5e1 !important; }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased">

    <!-- Header Istituzionale -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs no-print">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 h-20 flex justify-between items-center">
            <a href="/" class="flex items-center gap-3.5">
                <div class="w-11 h-11 bg-[#004b23] rounded-xl flex items-center justify-center text-white font-black text-xl shadow-sm">
                    🏛️
                </div>
                <div class="flex flex-col">
                    <span class="font-extrabold text-lg tracking-tight text-[#004b23]">DFN Prenotazioni</span>
                    <span class="text-xs text-slate-500 font-medium">Portale Eventi & Prenotazioni Culturali</span>
                </div>
            </a>
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="cursor-pointer bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-1.5 transition">
                    <span>🖨️</span> Stampa Biglietto
                </button>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-3xl w-full mx-auto px-4 sm:px-6 py-8">

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center gap-3 shadow-xs no-print">
                <span class="text-2xl">🎉</span>
                <div>
                    <h3 class="font-bold text-sm sm:text-base">Prenotazione registrata con successo!</h3>
                    <p class="text-xs sm:text-sm text-emerald-700">Conserva questa pagina o fai uno screenshot del codice QR sottostante da presentare all'ingresso.</p>
                </div>
            </div>
        @endif

        <!-- Card Biglietto Istituzionale FAI -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-md overflow-hidden print-shadow-none">

            <!-- Banner Superiore con Stato -->
            <div class="{{ $booking->status === 'confirmed' ? 'bg-[#004b23]' : 'bg-amber-600' }} text-white p-6 sm:p-7 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        @if($booking->status === 'confirmed')
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <span class="text-xs uppercase tracking-wider font-extrabold text-emerald-200">Prenotazione Confermata</span>
                        @else
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-200"></span>
                            <span class="text-xs uppercase tracking-wider font-extrabold text-amber-100">Verifica Tessera all'Ingresso</span>
                        @endif
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight">
                        {{ $event->title }}
                    </h1>
                </div>
                <div class="bg-white/15 px-4 py-2 rounded-xl text-center self-stretch sm:self-auto">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-white/80 block">Codice Prenotazione</span>
                    <span class="text-lg font-black tracking-widest">{{ $booking->booking_code }}</span>
                </div>
            </div>

            <!-- Corpo del Biglietto -->
            <div class="p-6 sm:p-8 space-y-6">

                <!-- Griglia Data / Orario / Luogo -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-6 border-b border-slate-100">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">📅 Data & Orario</span>
                        <div class="font-extrabold text-slate-900 text-base">
                            @if($booking->slot)
                                {{ $booking->slot->slot_date->format('d/m/Y') }}
                                <span class="text-[#004b23] block text-sm font-semibold">
                                    Turno: {{ substr($booking->slot->start_time, 0, 5) }} - {{ substr($booking->slot->end_time, 0, 5) }}
                                </span>
                            @else
                                {{ $event->event_date_start ? $event->event_date_start->format('d/m/Y') : 'Data stabilita' }}
                                <span class="text-[#004b23] block text-sm font-semibold">
                                    Dalle {{ substr($event->event_time_start ?? '10:00', 0, 5) }} alle {{ substr($event->event_time_end ?? '18:00', 0, 5) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">📍 Luogo di Ritrovo</span>
                        <div class="font-extrabold text-slate-900 text-base">
                            {{ $event->location ?? 'Sede dell\'evento' }}
                            @if($event->city)
                                <span class="text-slate-600 block text-sm font-semibold">{{ $event->city }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sezione Intestatario & QR Code -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-6 items-center pb-6 border-b border-slate-100">

                    <!-- Colonna QR Code -->
                    <div class="sm:col-span-5 flex flex-col items-center justify-center p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                        <div class="bg-white p-3 rounded-xl shadow-xs border border-slate-200 mb-2">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($booking->booking_code) }}"
                                 alt="QR Code Prenotazione"
                                 class="w-36 h-36 sm:w-40 sm:h-40 mx-auto">
                        </div>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Mostra al check-in</span>
                    </div>

                    <!-- Colonna Dettagli Intestatario e Quote -->
                    <div class="sm:col-span-7 space-y-3.5">
                        <div>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Intestatario</span>
                            <span class="text-base font-black text-slate-900">{{ $booking->customer_name }}</span>
                            <div class="text-xs text-slate-600 flex flex-wrap gap-2 mt-0.5">
                                <span>📧 {{ $booking->customer_email }}</span>
                                @if($booking->customer_phone)
                                    <span>• 📞 {{ $booking->customer_phone }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-100">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Partecipanti Registrati ({{ $booking->total_persons }})</span>
                            <div class="flex flex-wrap gap-2 text-xs font-bold">
                                @if($booking->persons_standard > 0)
                                    <span class="bg-slate-100 text-slate-800 px-2.5 py-1 rounded-lg">
                                        {{ $booking->persons_standard }}x Intero
                                    </span>
                                @endif
                                @if($booking->persons_fai > 0)
                                    <span class="bg-[#e74f30]/10 text-[#e74f30] border border-[#e74f30]/20 px-2.5 py-1 rounded-lg flex items-center gap-1">
                                        <span>🛡️</span> {{ $booking->persons_fai }}x Socio FAI
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Stato Tessere FAI -->
                        @if(!empty($booking->fai_cards) && count($booking->fai_cards) > 0)
                            <div class="pt-2 border-t border-slate-100">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Tessere FAI Dichiarate</span>
                                <div class="space-y-1">
                                    @foreach($booking->fai_cards as $card)
                                        <div class="flex items-center justify-between text-xs bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100">
                                            <span class="font-mono font-bold text-slate-700">
                                                Tessera: {{ $card['card_number'] ?? 'Non indicata' }}
                                                @if(!empty($card['member_name']))
                                                    <span class="text-slate-500 font-sans font-normal">({{ $card['member_name'] }})</span>
                                                @endif
                                            </span>
                                            @if(!empty($card['is_valid']))
                                                <span class="text-emerald-700 font-bold flex items-center gap-1">✔ Valida</span>
                                            @else
                                                <span class="text-amber-700 font-bold flex items-center gap-1" title="{{ $card['reason'] ?? '' }}">⚠️ Da verificare</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Quota da Saldare -->
                        <div class="pt-2 border-t border-slate-100 flex justify-between items-center">
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Contributo Totale</span>
                                <span class="text-xs text-slate-500">
                                    {{ $booking->payment_method === 'in_loco' ? 'Da versare al banchetto d\'accoglienza' : 'Metodo: ' . $booking->payment_method }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-xl font-black text-[#004b23]">
                                    €{{ number_format($booking->amount_due, 2) }}
                                </span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Istruzioni per il visitatore -->
                <div class="bg-emerald-50/60 rounded-2xl p-4 sm:p-5 border border-emerald-100/80 text-xs text-emerald-950 leading-relaxed space-y-2">
                    <div class="font-extrabold flex items-center gap-1.5 text-emerald-900 text-sm">
                        <span>ℹ️</span> Istruzioni per il giorno dell'evento
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-emerald-900/90">
                        <li>Presentati al punto di ritrovo con circa <strong>10 minuti di anticipo</strong> rispetto all'orario indicato.</li>
                        <li>Mostra questo biglietto digitale (o stampato) per la scansione del codice QR al banchetto FAI.</li>
                        @if($booking->persons_fai > 0)
                            <li>I partecipanti con quota ridotta Socio FAI devono esibire la <strong>tessera FAI</strong> in corso di validità al momento del check-in.</li>
                        @endif
                        @if($booking->amount_due > 0 && $booking->amount_paid == 0)
                            <li>Il contributo di partecipazione di <strong>€{{ number_format($booking->amount_due, 2) }}</strong> potrà essere versato in loco (contanti o POS).</li>
                        @endif
                    </ul>
                </div>

                <!-- Footer Azioni -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-2 no-print">
                    <a href="/evento/{{ $event->id }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition flex items-center gap-1">
                        <span>←</span> Torna alla scheda evento
                    </a>
                    <button onclick="window.print()" class="w-full sm:w-auto cursor-pointer bg-[#004b23] hover:bg-[#002f16] text-white text-xs sm:text-sm font-bold px-5 py-2.5 rounded-xl shadow-xs transition flex items-center justify-center gap-1.5">
                        <span>🖨️</span> Salva / Stampa Biglietto
                    </button>
                </div>

            </div>
        </div>

    </main>

    <!-- Footer Istituzionale -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500 mt-auto no-print">
        <p>&copy; {{ date('Y') }} DFN Prenotazioni — Piattaforma Ufficiale FAI Delegazione Territoriale</p>
    </footer>

</body>
</html>
