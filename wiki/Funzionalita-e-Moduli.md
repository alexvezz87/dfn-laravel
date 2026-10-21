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

### 5. Gestione Media & Ottimizzazione Immagini
- **Configurazione Dinamica Parametri Media (`ManageMediaSettings`)**:
  - Limite di upload configurabile per immagini (`media_max_image_mb`, range 1-50 MB, default 8 MB) e video per il Wall (`media_max_video_mb`, range 5-50 MB, default 50 MB).
  - Tetto hardware e server Docker configurato su 50MB (`upload_max_filesize = 50M`, `post_max_size = 50M`, `memory_limit = 256M`) per consentire upload senza errori HTTP o timeout Cloud Run.
  - Abilitazione/disabilitazione compressione e ottimizzazione automatica in tempo reale (`media_auto_compress`).
  - Risoluzione massima configurabile (`media_max_dimension_px`, opzioni: HD 1280px, Full HD 1920px, 2K 2560px).
  - Qualità di compressione modulare (`media_quality_percent`, range 50-95%, default 82%).
  - Generazione e conversione automatica nel formato WebP (`media_convert_webp`) per accelerare i tempi di caricamento e le prestazioni Core Web Vitals.
- **Modalità Doppia di Upload**:
  - **Modalità Trasparente & Ottimizzata (`media_auto_compress === true`)**: FilePond accetta file pesanti fino al tetto del server (50 MB) senza generare errori di blocco. Il client e il backend ridimensionano e comprimono automaticamente l'immagine al di sotto della soglia target con conversione WebP. Un hook in `CreateEvent` ed `EditEvent` elabora il file e invia una notifica toast informativa del risparmio di peso.
  - **Modalità Rigida (`media_auto_compress === false`)**: Il componente `FileUpload` applica il limite rigido (`maxSize`) pari alla soglia configurata, bloccando all'ingresso i file oltre soglia senza alcuna manipolazione.
- **Pipeline Client-Side & Server-Side**:
  - FilePond in Filament applica ridimensionamento client-side in base alla configurazione attiva su locandina principale e gallery.
  - Action backend `OptimizeImageAction` con libreria nativa GD per ridimensionamento proporzionale, tracciamento byte e conversione WebP a zero dipendenze esterne.
  - **Preservazione Nome Originale & Anti-Collisione**: i file salvati non utilizzano più hash casuali illeggibili a 40 caratteri, ma preservano il nome originale trasformato in slug web-safe (`Str::slug()`), con suffissi incrementali automatici in caso di omonimia (`nome-1.ext`, `nome-2.ext`).
  - **Refresh Immediato del Form**: al salvataggio in `EditEvent`, l'invocazione di `$this->refreshFormData(['featured_image', 'gallery_images'])` sincronizza istantaneamente i box di upload con i percorsi `.webp` effettivi, eliminando lo svuotamento temporaneo del preview senza richiedere refresh F5.

### 6. Libreria Media Centralizzata (`MediaResource` stile WordPress)
- **Doppia Modalità di Visualizzazione (Griglia 5-6 Colonne & Tabella Nativa Monoriga)**:
  - **Architettura Disaccoppiata (`Table::content()`)**: la visualizzazione è gestita tramite il metodo nativo `Table::content()`. In modalità Griglia restituisce la vista dedicata `media-grid.blade.php`, mentre in modalità Elenco restituisce `null`, permettendo a Filament di generare la tabella HTML nativa `<table>` senza layout card forzati.
  - **Visualizzazione a Griglia ad Alta Visibilità (Default)**: griglia CSS fluida a 5-6 colonne (`grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4`) priva di checkbox laterali o barre azioni ingombranti. Le tessere sono grandi e ben visibili (~200×200px) con riempimento 100% dell'immagine (`object-cover w-full h-full`), micro-zoom in hover e overlay scuro con esclusivamente l'icona matita centrata (`heroicon-m-pencil-square`) per aprire lo Slide-Over.
  - **Visualizzazione ad Elenco (Tabella Nativa HTML Monoriga)**: tabella classica con intestazioni `<th>` e ogni record rigorosamente su una sola riga orizzontale (`<tr>`), con miniatura 40×40 px, titolo troncato monoriga a 40 caratteri, badge compatti e icone azione `iconButton` affiancate.
  - **Switcher Icone Compatto in Testata**: due pulsanti icona affiancati compatti (`[ ⊞ ] [ ☰ ]`) per alternare istantaneamente la vista via Livewire senza layout shift.
- **Pannello Modifica e Dettagli al Clic (Slide-Over stile WordPress)**:
  - Cliccando su qualsiasi elemento della griglia o dell'elenco (`recordAction('edit')` o clic sulla tessera) si apre un cassetto laterale (*Slide-Over*) a comparsa rapida.
  - Presenta l'anteprima dell'immagine (o player video), tutti i metadati editoriali modificabili (Titolo, Alt Text per SEO, Didascalia, Collezione), i dati tecnici di sola lettura (Dimensione formattata, Risoluzione pixel, MIME type) e il campo URL Pubblico con pulsante di copia negli appunti con 1 clic.
- **Ottimizzazione Automatica & Conversione WebP su Caricamento Diretto (`CreateMedia`)**:
  - Il caricamento diretto dalla Libreria Media (`CreateMedia`) è integrato con `OptimizeImageAction`.
  - Le immagini caricate vengono automaticamente convertite in formato WebP, scalate alle dimensioni massime consentite (es. 1920px), compresse alla qualità target (es. 82%) e salvate con gestione anti-collisione sia sul nome originario sia sulla controparte WebP.
  - Invio immediato di una notifica toast e di una notifica persistente nella campanella della topbar che riepiloga il risparmio in byte e la percentuale di riduzione ottenuta.
- **Persistenza su Google Cloud Storage (GCS Volume Mount)**:
  - Risoluzione del problema di stateless container su Cloud Run: la cartella `/app/storage/app/public` è montata tramite Cloud Storage FUSE (`--execution-environment=gen2`, `--add-volume` e `--add-volume-mount`) su un bucket dedicato di Google Cloud Storage (`${PROJECT_ID}-media-staging`).
  - Tutti i file multimediali e le locandine caricate permangono intatti e disponibili attraverso ogni deploy e riavvio del container.
- **Archivio Asset Unificato & Pulizia Fisica**:
  - Gestione completa di immagini e video attraverso la risorsa `MediaResource` (`/admin/media`), raggruppata sotto il menu dedicato **Media**.
  - Pre-compilazione automatica del Titolo se lasciato vuoto in caricamento.
  - L'eliminazione di un singolo elemento o la cancellazione massiva rimuove fisicamente il file dallo storage GCS, prevenendo sprechi di spazio.
- **Tracciamento Automatico da Eventi**:
  - In `CreateEvent` ed `EditEvent`, il salvataggio di locandine (`featured_image`), slider galleria (`gallery_images`) o video/foto del Wall (`wall_media`) esegue la registrazione automatica via `MediaItem::createFromStoragePath(...)`, garantendo che tutti gli asset caricati tramite gli eventi siano immediatamente censiti nella Libreria Media.

### 7. Centro Notifiche Persistente (Topbar)
- **Campanella Interattiva**:
  - Integrazione nativa delle Database Notifications di Filament nel pannello amministrativo (`AdminPanelProvider`).
  - Campanella collocata nella topbar superiore con badge rosso dinamico del conteggio delle notifiche non lette.
  - Polling automatico ogni 30 secondi (`databaseNotificationsPolling('30s')`) per aggiornare lo stato senza ricaricare la pagina.
  - Drawer laterale a comparsa con lo storico di tutti gli avvisi ricevuti, possibilità di contrassegnare singolarmente o in blocco le notifiche come lette.
- **Notifiche di Sistema**:
  - Registrazione persistente dell'esito delle ottimizzazioni immagini (risparmio di peso in MB e percentuale) inviata direttamente all'utente amministratore autenticato.

---

## 💡 Idee di Sviluppo & Proposte Future (Backlog)
Le idee in fase di discussione o PoC vengono elencate qui in attesa di essere trasformate in Feature approvate:

- *Nessuna proposta attualmente pendente. Apri una Issue con label `type: idea` per proporne una.*

---

## 📝 Regola di Aggiornamento
Ogni nuova Feature aggiunta o modificata nel codice deve essere documentata in questa pagina prima del completamento del task.
