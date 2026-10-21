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
- **Tabellone Eventi Full-Width**: visualizzazione a tutto schermo (`getMaxContentWidth() => 'full'`) con rimozione della colonna tariffe per eliminare wrapping e azzerare le andate a capo indesiderate.

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
  - **Densità Griglia Multimediale Dinamica (`media_grid_columns`)**: numero di colonne della griglia configurabile da 4 a 10 colonne su schermi desktop (default 7 consigliato), calcolato dinamicamente tramite CSS Grid nativo.
- **Modalità Doppia di Upload**:
  - **Modalità Trasparente & Ottimizzata (`media_auto_compress === true`)**: FilePond accetta file pesanti fino al tetto del server (50 MB) senza generare errori di blocco. Il client e il backend ridimensionano e comprimono automaticamente l'immagine al di sotto della soglia target con conversione WebP. Un hook in `CreateEvent` ed `EditEvent` elabora il file e invia una notifica toast informativa del risparmio di peso.
  - **Modalità Rigida (`media_auto_compress === false`)**: Il componente `FileUpload` applica il limite rigido (`maxSize`) pari alla soglia configurata, bloccando all'ingresso i file oltre soglia senza alcuna manipolazione.
- **Pipeline Client-Side & Server-Side**:
  - FilePond in Filament applica ridimensionamento client-side in base alla configurazione attiva su locandina principale e gallery.
  - Action backend `OptimizeImageAction` con libreria nativa GD per ridimensionamento proporzionale, tracciamento byte e conversione WebP a zero dipendenze esterne.
  - **Preservazione Nome Originale & Anti-Collisione**: i file salvati non utilizzano più hash casuali illeggibili a 40 caratteri, ma preservano il nome originale trasformato in slug web-safe (`Str::slug()`), con suffissi incrementali automatici in caso di omonimia (`nome-1.ext`, `nome-2.ext`).
  - **Refresh Immediato del Form**: al salvataggio in `EditEvent`, l'invocazione di `$this->refreshFormData(['featured_image', 'gallery_images'])` sincronizza istantaneamente i box di upload con i percorsi `.webp` effettivi, eliminando lo svuotamento temporaneo del preview senza richiedere refresh F5.

### 6. Libreria Media Centralizzata (`MediaResource` stile WordPress)
- **Doppia Modalità di Visualizzazione (Griglia Dinamica & Tabella Nativa Monoriga)**:
  - **Architettura Disaccoppiata (`Table::content()`)**: la visualizzazione è gestita tramite il metodo nativo `Table::content()`. In modalità Griglia restituisce la vista dedicata `media-grid.blade.php`, mentre in modalità Elenco restituisce `null`, permettendo a Filament di generare la tabella HTML nativa `<table>` senza layout card forzati.
  - **Visualizzazione a Griglia CSS Dinamica (`--dfn-grid-cols`)**: layout a griglia calcolato matematicamente dal browser via CSS Grid nativo (`.dfn-media-grid`) in base all'impostazione configurata in *Media & Upload* (da 4 a 10 colonne, default 7), con `gap: 12px`, 2 colonne su mobile (<=640px) e 4 colonne su tablet (641-1024px), privo di checkbox laterali.
  - **Hover Matita e Aspect-Ratio 1:1 Garantiti**: classi CSS dedicate (`.dfn-media-card`, `.dfn-media-overlay`, `.dfn-media-pencil-btn`) che assicurano riquadri quadrati perfetti con micro-zoom, overlay semitrasparente e icona matita centrata al passaggio del cursore indipendente dalle regole di purge di Tailwind.
  - **Visualizzazione ad Elenco (Tabella Nativa HTML Monoriga)**: tabella classica con intestazioni `<th>` e ogni record rigorosamente su una sola riga orizzontale (`<tr>`), con miniatura 40×40 px, titolo troncato monoriga a 40 caratteri, badge compatti e icone azione `iconButton` affiancate.
  - **Switcher Icone Compatto in Testata**: due pulsanti icona affiancati compatti (`[ ⊞ ] [ ☰ ]`) per alternare istantaneamente la vista via Livewire senza layout shift.
- **Popup Dettagli Allegato Compatto 50/50 Scrollabile (`modalWidth('5xl')` stile WordPress)**:
  - Cliccando su qualsiasi elemento della griglia o riga dell'elenco (`mountTableAction('edit')`) si apre una finestra modale centrale a comparsa rapida (`Dettagli Allegato: Titolo File`).
  - **Layout Paritetico 50/50**: struttura a due colonne bilanciate (`Grid::make(2)`) con colonna sinistra dedicata all'anteprima visiva compatta (`max-h-[260px] object-contain`) e colonna destra per metadati (Titolo, Alt Text SEO, Didascalia, Collezione) e dati tecnici (Peso formattato, Risoluzione, MIME Type, Link URL copiabile con 1 clic).
  - **Scroll Verticale Fluido su `.fi-modal-content`**: scroll garantito al 100% sul container interno della modale Filament (`overflow-y: auto !important`, `max-height: calc(88vh - 145px)` con `padding-bottom: 20px`), garantendo l'accessibilità immediata a tutti i campi e ai pulsanti di salvataggio.
  - **Localizzazione Completa, Spaziatura Superiore e Allineamento Bottoni a Destra**: interfaccia interamente in lingua italiana (`locale => 'it'`), pulsanti footer modale spaziati dal contenuto con `padding-top: 20px` su `.fi-modal-footer` e saldamente allineati a destra del popup (`flex-direction: row-reverse !important; justify-content: flex-start !important;`) con etichette esplicite: *"Salva Modifiche"* e *"Annulla"*.
  - **Pulsante Elimina Dedicato nel Footer**: integrata azione `modal_delete` (`DeleteAction`) in colore rosso pericolo (`color('danger')`) con icona cestino, modale di conferma in italiano (*"Elimina File Definitivamente"*) e rimozione contestuale sia dal database che dallo storage fisico GCS con chiusura della modale.
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
