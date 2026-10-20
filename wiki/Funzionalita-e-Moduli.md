# ⚙️ Funzionalità e Moduli

Questo documento raccoglie la descrizione dei moduli funzionali, le regole di business e i flussi operativi implementati nell'applicazione.

---

## 🎯 Moduli Principali

### 1. Gestione Eventi e Luoghi
- Configurazione di eventi con data d'inizio e fine, orari di apertura e chiusura.
- Impostazione di politiche tariffarie differenziate: quota standard vs quota agevolata soci FAI.
- Controllo automatico data di apertura prenotazioni (`booking_opening_date`) con countdown.
- Gestione flessibile della modalità di accesso: *Flusso Libero* (con capienza complessiva) o *Fasce Orarie* (a turni).
- Selettore del layout pubblico della scheda: *Automatico*, *Layout 1 — Galleria* o *Layout 2 — Locandina*, con miniature visive scheletriche interattive in Filament.
- Anteprima pubblica fedele 1:1 (`/evento/{event}`) accessibile direttamente da Filament, con visualizzazione della locandina, coordinate logistiche, posti disponibili e selettore quote partecipanti intero / socio FAI.
- Gestione completa asset multimediali: locandina principale (`featured_image`), slider immagini pre-evento (`gallery_images`), e wall ricordi con foto/video post-evento (`wall_media`).
- Modalità test (`is_test_event`) con recapito notifiche a email dedicata e toggle recensioni front-end.
- Sistema integrato di tooltip popup informativi su tutti i campi di configurazione per assistere gli operatori nell'amministrazione.
- Funzione rapida di duplicazione evento con clonazione automatica dei turni (in stato bozza e azzeramento prenotazioni).

### 2. Generazione e Controllo Slot
- Generazione automatica o manuale dei turni orari di visita.
- Parametrizzazione della durata turno (`slot_duration`), capienza base (`slot_capacity`) e bonus capienza (`slot_bonus`).
- Controllo capienza in tempo reale: blocco delle prenotazioni che eccedono i posti residui.

### 3. Flusso Prenotazioni & Funnel Pubblico (Bookings)
- **Funnel Pubblico Interattivo (`/evento/{event}`)**:
  - Selezione partecipanti differenziata (Interi e Soci FAI) con aggiornamento dinamico del contributo totale.
  - Selettore dei turni orari con disponibilità posti residui e disabilitazione automatica degli slot esauriti o con capienza insufficiente.
  - Modulo anagrafico visitatore: Nome, Cognome, Email, Cellulare e Note facoltative.
  - Gestione atomica della concorrenza (con `lockForUpdate` a database) per prevenire qualsiasi rischio di overbooking.
- **Convalida Tessere Soci FAI**:
  - Input dinamici per ciascun socio FAI dichiarato, con pulsante di verifica live (`/api/check-fai-card`) contro la tabella `fai_members`.
  - Se tutte le tessere sono valide: stato prenotazione `confirmed`.
  - Se vi sono tessere non censite o scadute: la prenotazione viene comunque registrata con stato `pending_fai` per verifica e regolarizzazione manuale all'ingresso.
- **Biglietto Digitale Pubblico (`/prenotazione/{booking_code}`)**:
  - Schermata di riepilogo con codice univoco alfanumerico (es. `DFN-2026-XXXX`).
  - QR Code dinamico generato per scansione rapida all'ingresso con la PWA scanner.
  - Dettaglio orari, indirizzo location e istruzioni per l'arrivo.
  - Supporto nativo per stampa / salvataggio (`window.print()`).
- **Gestione Check-in in Loco**:
  - Registrazione ingresso dei visitatori (`checkin_at`, `checkin_status`) tramite back-office Filament o scanner mobile.

### 4. Verifica Soci FAI
- Validazione in tempo reale del numero di tessera inserito contro la tabella `fai_members`.
- Controllo data di scadenza della tessera per garantire l'accesso alla tariffa soci.

---

## 💡 Idee di Sviluppo & Proposte Future (Backlog)
Le idee in fase di discussione o PoC vengono elencate qui in attesa di essere trasformate in Feature approvate:

- *Nessuna proposta attualmente pendente. Apri una Issue con label `type: idea` per proporne una.*

---

## 📝 Regola di Aggiornamento
Ogni nuova Feature aggiunta o modificata nel codice deve essere documentata in questa pagina prima del completamento del task.
