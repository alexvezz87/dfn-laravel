# 🖥️ Guida Operativa Filament Admin

Questa guida è destinata agli operatori di backoffice e agli sviluppatori che gestiscono il pannello amministrativo **Filament 3** (replica evoluta della dashboard WordPress/Bedrock).

---

## 🔑 Accesso al Pannello
- URL: `/admin` (es. `https://dfn-laravel-staging.dfnprenotazioni.it/admin`)
- Requisiti: account utente con credenziali autorizzate ad accedere al pannello.

---

## 🧭 Struttura del Menu Laterale (Sidebar)

La navigazione del pannello è organizzata in tre gruppi funzionali distinti:
1. **`FAI Prenotazioni`** (operativo core):
   - `Eventi`: catalogo, configurazione logistica, tariffe, turni e duplicazione eventi.
   - `Verifica Prenotazioni FAI`: monitoraggio ordini e validazione soci FAI.
   - `Gestione Turni`: gestione e disponibilità delle fasce orarie.
   - `Soci FAI`: anagrafica soci tesserati e verifica scadenze.
2. **`Media`** (gestione asset multimediali):
   - `Libreria Media`: archivio centralizzato di file, foto e video con anteprime, metadati ed eliminazione fisica.
3. **`Impostazioni`** (configurazioni di sistema):
   - `Media & Upload`: parametri globali di upload, compressione automatica e formati immagini.

---

## 📋 Risorse Gestite

### 1. Risorsa Eventi (`EventResource`)
- **Tabellone a Tab (in stile Bedrock)**:
  - 🟢 **Eventi in Programma & In Corso**: mostra gli eventi con `event_date_end >= oggi` (ordinati in ordine cronologico crescente, il più vicino in cima), con badge dinamico del conteggio.
  - ⚪ **Eventi Conclusi & Passati**: raggruppa lo storico degli eventi terminati (`event_date_end < oggi`), ordinati dal più recente al più vecchio.
  - 📋 **Tutti gli Eventi**: panoramica complessiva di tutti i record a catalogo.
- **Colonne del Tabellone (Layout Full-Width a Tutto Schermo)**:
  - **Evento**: titolo in grassetto senza wrapping forzato, con location e città mostrati come descrizione sottostante.
  - **Date & Orari**: intervallo data inizio/fine formattato in italiano (`d/m/Y`) e orari di accesso.
  - **Tipologia**: badge semantico per *Fasce Orarie* (`info`) o *Flusso Libero* (`primary`), con indicazione della modalità di allocazione (*Automatica* o *Self-selection*).
  - **Capacità**: conteggio posti occupati rispetto alla capienza massima configurata (`occupati / totale`).
  - **Stato**: badge per *Pubblicato* (verde), *Bozza* (grigio), *Archiviato* (ambra).
- **Azioni Rapide di Riga**:
  - 🎟️ **Prenotazioni**: link diretto alla lista prenotazioni prefiltrata per l'evento selezionato.
  - ✏️ **Modifica**: apertura della scheda di dettaglio/modifica dell'evento.

- **Form di Creazione / Modifica Evento (Parità 100% con WordPress Bedrock & Tema FAI)**:
  - **Header & Azioni della Pagina (`EditEvent.php`)**:
    - Titolo istituzionale: **Modifica Evento FAI** (o *Configura Nuovo Evento FAI* in creazione).
    - 👁️ **Anteprima Evento**: apre in una nuova scheda l'anteprima pubblica fedele 1:1 della scheda evento (`/evento/{event}`), con locandina, orari, posti disponibili e selettore interattivo partecipanti intero/socio FAI.
    - 🎟️ **Tabellone Prenotazioni**: link rapido alla schermata `ManageEventBookings` per l'evento corrente.
    - 📄 **Duplica questo Evento**: azione modale con conferma che genera istantaneamente una copia identica dell'evento in stato *Bozza*, duplicando anche tutti i relativi turni/slot (resettando i posti prenotati a 0).
    - ⬅️ **Torna al Tabellone**: link diretto all'indice degli eventi.
    - 💾 **Pulsante Principale Verde FAI**: etichetta personalizzata **`Aggiorna Configurazione`** (o *Crea Evento FAI*).
  - **Colonna Sinistra (Dettagli Principali & Logistica)**:
    - *Informazioni Principali dell'Evento*: titolo dell'evento (senza alcun campo WooCommerce o dipendenza da e-commerce esterno).
    - *Logistica & Orari*: date inizio/fine, orari apertura/chiusura, data di apertura prenotazioni con countdown e tooltip modale esplicativo, indirizzo di ritrovo (`location`), comune/città e descrizione con RichEditor.
    - *Tipologia Accesso & Algoritmo di Allocazione*: selezione tra *Flusso Libero (Senza fasce)* con campo *Capacità Massima Evento*, oppure *Fasce Orarie (a turni)* con durata turno (min), capacità standard, capacità bonus staff, e orari del primo/ultimo turno. Tutti i campi dispongono di tooltip modali con popup `(?)`.
  - **Colonna Destra (Sidebar Modulare - 7 Box Ufficiali Bedrock)**:
    - 1. **Pubblica**: stato evento (Pubblicato/Bozza/Archiviato), workflow approvazione (con popup esplicativo), stato prenotazioni (Aperte/Chiuse/In Arrivo con popup), modalità pagamento (Online, In Loco, Entrambi con popup), timeout annullamento automatico in ore (con popup), modalità test isolata su email dedicata, toggle *Mostra Recensioni nel Front-End*.
    - 2. **Immagine in Evidenza**: upload e preview della locandina o copertina principale dell'evento. I file vengono memorizzati con il loro nome leggibile slugificato (con anti-collisione automatica). Al salvataggio, il box anteprima si aggiorna istantaneamente riflettendo l'immagine ottimizzata senza richiedere F5.
    - 3. **Galleria Slider (Pre-Evento)**: upload multiplo per le foto descrittive mostrate nello slider della scheda evento (jpg, png, webp), con preservazione dei nomi leggibili e refresh automatico al salvataggio.
    - 4. **Wall dei Ricordi (Post-Evento)**: caricamento multimediale di foto e video (mp4, webm) con popup esplicativo sul funzionamento post-evento.
    - 5. **Layout Pagina Dettaglio**: selettore visivo interattivo con wireframe scheletrici delle tre opzioni (*Automatico*, *Layout 1 — Galleria* con slider in cima, *Layout 2 — Locandina* con locandina a sinistra e form a destra).
    - 6. **Listino Contributi**: Biglietto Standard (€) e Quota agevolata Socio FAI (€).
    - 7. **Staff & Note Interne**: note operative e istruzioni riservate alla cassa e ai volontari.
  - **Tooltip Modali Esplicativi (`(?)`)**:
    - Ogni campo rilevante include un pulsante circolare verde `(?)` che apre un popup con intestazione verde scuro FAI, testo dettagliato, box di suggerimento verde (`Caso d'uso tipico`) e box di attenzione ambra.
  - **Stile Visivo FAI Personalizzato (`dfn-admin-custom.css`)**:
    - Palette istituzionale FAI: Forest Green (`#004b23`), Dark Green (`#002f16`), Ocra/Oro (`#c69c3a`), Arancio FAI (`#ea580c`).
    - Intestazioni delle schede evidenziate con sfondo `#f8fafc` e testi in verde FAI bold.
    - Focus ring, pulsanti principali e tooltip buttons conformi al brand.

---

### 2. Gestione Prenotazioni per Evento (`ManageEventBookings`)
- **Accesso**: dal Tabellone Eventi, cliccando sul pulsante verde **`[Prenotazioni]`** in corrispondenza dell'evento desiderato (es. *United Gospel 2026*).
- **Intestazione con Ritorno Rapido**: pulsante in alto a sinistra **`[← Torna agli Eventi]`** per tornare immediatamente al Tabellone.
- **Riepilogo Statistico (KPI Cards calcolate per l'evento)**:
  - 🎟️ **PRENOTAZIONI ATTIVE**: totale confermato per l'evento selezionato.
  - 👥 **POSTI OCCUPATI**: posti occupati dai partecipanti registrati a questo evento.
  - 📊 **TASSO DI OCCUPAZIONE**: percentuale calcolata in tempo reale sulla capienza totale dell'evento (es. `9% (90 / 948)`).
  - ⚠️ **TESSERE FAI DA VERIFICARE**: richieste pendenti di approvazione tessera per questo evento.
- **Banner Slot / Turno con Progress Bar**:
  - Mostra tipologia (*Flusso Libero* o *Fasce Orarie*), orari del turno e barra di avanzamento dinamica della capienza con conteggio partecipanti e prenotazioni.
- **Navigazione a Tab (in stile `dfn-slot-manager`)**:
  - 🟢 **Prenotazioni Attive & Confermate**: partecipanti validi confermati.
  - ⚠️ **Storico Movimenti & Tentativi (Incompleti / Annullati)**: tentativi non andati a buon fine, pagamenti pendenti o ordini cancellati.
  - 📋 **Tutte le Prenotazioni**: panoramica completa per l'evento.
- **Colonne della Tabella**:
  - **Ordine #**: codice numerico ordine WooCommerce/Stripe (es. `#2455`).
  - **Cliente**: nome intestatario in grassetto, email sottostante.
  - **Qualifica**: badge arancione in evidenza **`SOCIO FAI`** per i soci, altrimenti `Standard`.
  - **Telefono**: recapito telefonico con link diretto `tel:`.
  - **Biglietti**: numero partecipanti per la prenotazione.
  - **Pagamento**: badge verde `✔ Pagato` con metodo (Stripe, Dfn_in_loco) o `In attesa`.
  - **Prenotazione registrata**: data e ora della prenotazione (`d/m/Y - H:i`).
- **Azioni Rapide**:
  - 🎟️ **Check-in**: per registrare l'ingresso del visitatore al banchetto.
  - 🛡️ **Approva FAI**: per convalidare la tessera socio FAI in 1 clic.

---

### 3. Verifica Prenotazioni FAI (`BookingResource`)
- **Navigazione**: menu laterale *FAI Prenotazioni* > *Verifica Prenotazioni FAI* (con badge rosso del conteggio in attesa).
- Schermata centralizzata per gli operatori di segreteria per verificare e approvare rapidamente le tessere soci FAI caricate dagli utenti.

---

### 4. Gestione Turni (`SlotResource`)
- Visualizzazione e gestione dei singoli turni orari per ciascun evento.
- Monitoraggio della capienza e dei posti residui per ogni turno.

---

### 5. Anagrafica Soci FAI (`FaiMemberResource`)
- **Navigazione**: menu laterale *FAI Prenotazioni* > *Soci FAI*.
- **Elenco e Ricerca**:
  - Visualizzazione dei 65 soci FAI sincronizzati dal database di produzione.
  - Ricerca rapida per numero tessera (con funzione di copia con 1 clic), nominativo, email o telefono.
  - Colonna Scadenza con colore dinamico: verde se tessera in corso di validità, rosso se scaduta.
  - Badge di stato: *Attivo* (verde) o *Inattivo* (rosso).
- **Filtri di Tabella**:
  - Filtro *Solo Tessere Valide*: estrae i soci con stato attivo e data di scadenza futura.
  - Filtro *Solo Tessere Scadute*: isola le tessere da rinnovare.

---

### 6. Libreria Media (`MediaResource`)
- **Navigazione**: menu laterale *Media* > *Libreria Media*.
- **Scopo**: offre un archivio centralizzato in stile WordPress per visualizzare, caricare, organizzare ed eliminare tutti i file multimediali utilizzati sul portale.
- **Switcher Modalità di Visualizzazione (in Testata)**:
  - ⊞ **Griglia** / ☰ **Elenco**: due pulsanti icona compatti affiancati (`heroicon-o-squares-2x2` e `heroicon-o-bars-3`) con tooltip informativo per alternare all'istante tra la griglia fotografica WordPress e la vista tabellare compatta.
- **Visualizzazione a Griglia 1:1 WordPress (Colonne Dinamiche da Impostazioni)**:
  - **Tessere Fluide & Dinamiche (`aspect-square`)**: disposizione a scacchiera fluida gestita tramite CSS Grid nativo (`.dfn-media-grid` con `--dfn-grid-cols`), con numero di colonne su desktop regolabile direttamente da *Impostazioni &rarr; Media & Upload* (da 4 a 10 colonne, default 7 standard), 2 colonne su mobile e 4 su tablet. Tessere quadrate pure senza barre laterali di checkbox per massimizzare la visibilità.
  - **Hover Matita Indipendente**: classi CSS dedicate (`.dfn-media-card`, `.dfn-media-overlay`, `.dfn-media-pencil-btn`) che garantiscono al 100% l'aspetto quadrato, il micro-zoom dell'immagine e la comparsa al passaggio del mouse dell'icona matita centrata su cerchio bianco in rilievo.
- **Popup Dettagli Allegato Compatto 50/50 Scrollabile (`modalWidth('5xl')` stile WordPress)**:
  - Cliccando su qualsiasi tessera della griglia o riga dell'elenco (`mountTableAction('edit')`), si apre al centro una finestra modale paritetica a due colonne (`Grid::make(2)`) in puro stile WordPress con titolo personalizzato (`Dettagli Allegato: Titolo File`).
  - **Colonna Sinistra (50%)**: anteprima dell'immagine compatta e centrata ad alta leggibilità (`max-h-[260px] object-contain`) o player video interattivo, priva di box di upload che disturbino la consultazione.
  - **Colonna Destra (50%)**: metadati editoriali modificabili (Titolo, Alt Text per SEO `alt_text`, Didascalia `caption`, Collezione), dati tecnici di sola lettura (Dimensione formattata, Risoluzione in pixel, MIME Type) e Link URL Pubblico con pulsante di copia negli appunti con un clic.
  - **Scroll Verticale Fluido Garantito**: applicato direttamente sul container interno `.fi-modal-content` (`overflow-y: auto !important`, altezza massima `calc(88vh - 130px)` con `padding-bottom: 24px`), consentendo di scorrere tutti i campi e accedere comodamente ai tasti di azione.
  - **Comandi Footer Modale Allineati a Destra**: pulsanti disposti ordinatamente in basso a destra (`.fi-modal-footer-actions { justify-content: flex-end }`):
    - **Pulsante Salva Modifiche**: bottone primario verde per salvare i metadati aggiornati.
    - **Pulsante Annulla**: chiude la modale senza salvare.
    - **Pulsante Elimina (Rosso Pericolo)**: azione extra integrata nel footer (`DeleteAction` con icona cestino `heroicon-o-trash`), che richiede conferma esplicita in italiano (*"Elimina File Definitivamente"*) e rimuove definitivamente il file da database e storage fisico GCS, chiudendo contestualmente la modale.
- **Visualizzazione Tabella / Elenco Nativa (Singola Riga Orizzontale Monoriga)**:
  - **Tabella HTML Nativa**: tabella classica `<table>` con intestazioni `<th>` e righe orizzontali `<tr>` monoriga, eliminando definitivamente le card verticali impilate.
  - **Miniatura**: anteprima quadrata arrotondata 40×40 px con visualizzazione fedele dell'immagine o icona per video/documenti.
  - **Titolo Monoriga**: titolo descrittivo troncato a 40 caratteri con tooltip al passaggio del mouse che mostra il nome originario del file.
  - **Collezione**: badge compatto colorato per distinguere l'ambito (`featured`, `gallery`, `wall`, `uploads`, `general`).
  - **MIME Type**: badge grigio con il tipo di file (es. `image/webp`, `image/jpeg`, `video/mp4`).
  - **Dimensione Formattata**: badge blu con peso in KB/MB con ordinamento per byte reali.
  - **Risoluzione**: indicazione di larghezza per altezza (es. `1920 × 1080 px`).
  - **Data di Caricamento**: timestamp formattato `d/m/Y H:i`.
  - **Azioni Compatte (`iconButton`)**: pulsanti ad icona senza testo per Modifica (Popup centrale 5xl), Copia Link negli appunti ed Eliminazione.
- **Filtri di Tabella**:
  - Filtro per *Tipo Contenuto* (*Tutti*, *Immagini*, *Video*).
  - Filtro per *Collezione* (*Locandine*, *Galleria Slider*, *Wall dei Ricordi*, *Caricamenti Diretti*, *Generale*).
- **Caricamento Nuovo Media con Ottimizzazione WebP (`CreateMedia`)**:
  - Pulsante in alto a destra *Carica Nuovo Media*.
  - Upload drag-and-drop con memorizzazione su storage preservando il nome file originale in formato slug web-safe con gestione anti-collisione incrementale (sia per estensione originale sia per controparte `.webp`).
  - Pre-compilazione automatica del Titolo: se il campo *Titolo* viene lasciato vuoto, il sistema imposta automaticamente il nome originale del file senza estensione.
  - **Ottimizzazione Automatica WebP**: conversione automatica delle immagini in WebP, ridimensionamento alla risoluzione configurata (es. 1920px), compressione e calcolo dinamico di dimensioni, mime type e risoluzione.
  - **Notifiche di Risparmio**: notifica toast immediata e notifica permanente nella campanella topbar con i byte risparmiati e la percentuale di compressione.

---

### 7. Impostazioni Media & Upload (`ManageMediaSettings`)
- **Navigazione**: menu laterale *Impostazioni* > *Media & Upload* (icona slider/regolazioni).
- **Scopo**: permette agli amministratori di regolare in autonomia le policy di upload dei file, la compressione automatica delle immagini e la densità della libreria media senza dover intervenire sul server o su file di codice.
- **Sezioni di Configurazione**:
  - **Limiti di Caricamento (Upload)**:
    - *Dimensione Massima Immagini*: dimensione massima accettata (in MB, range 1-50 MB) per locandina principale e foto dello slider descrittivo.
    - *Dimensione Massima Video*: dimensione massima per i video caricati nel Wall dei Ricordi (in MB, range 5-50 MB).
  - **Compressione & Ottimizzazione Automatica**:
    - *Attiva compressione e ottimizzazione automatica immagini*: toggle ON/OFF per prevenire upload di file pesanti non ottimizzati (es. foto ad altissima risoluzione non compresse).
    - *Risoluzione Massima*: risoluzione massima consentita per il lato lungo (1280px HD, 1920px Full HD raccomandato, 2560px 2K).
    - *Qualità Compressione*: percentuale di qualità JPEG/WebP (default 82%).
    - *Converti automaticamente in WebP*: conversione al formato WebP per velocizzare significativamente il caricamento delle pagine front-end.
  - **Visualizzazione Libreria Media**:
    - *Numero di Colonne Griglia (Desktop)*: selettore della densità a scacchiera su schermi desktop da 4 a 10 colonne (4 molto grandi, 5 grandi, 6 medio-grandi, 7 standard consigliato, 8 compatte, 9 molto compatte, 10 piccole).
- **Comportamento dei Campi Upload (`featured_image` e `gallery_images`) in EventResource**:
  - **Con Ottimizzazione Automatica ATTIVA**: FilePond accetta file fino al tetto del server (50 MB) senza bloccare l'operatore con messaggi d'errore FilePond `File is too large`. Durante l'upload e il salvataggio in `CreateEvent` ed `EditEvent`, il sistema ridimensiona l'immagine (client-side) e la ottimizza/converte in WebP (backend), mostrando una notifica con l'avvenuto risparmio di peso. Helper text: `ℹ️ Ottimizzazione automatica attiva: le immagini pesanti verranno ridimensionate e compresse sotto i X MB`.
  - **Con Ottimizzazione Automatica DISATTIVATA**: FilePond applica il limite rigido impostato dall'amministratore (es. 4 MB) bloccando all'ingresso i file superiori alla soglia senza modifiche. Helper text: `⚠️ Limite massimo rigido: X MB per immagine`.
- **Pulsante Salva Impostazioni**: salva immediatamente a database nella tabella `settings` con gruppo `media` e notifica di successo.

---

### 8. Centro Notifiche Topbar & Campanella
- **Collocazione**: icona a campanella nella barra superiore (topbar) a destra, accanto al menu profilo operatore.
- **Contatore Notifiche Non Lette**: badge circolare rosso indicante il numero esatto di notifiche pendenti.
- **Funzionamento & Polling**:
  - Il sistema interroga il backend ogni 30 secondi in background (`databaseNotificationsPolling('30s')`) per segnalare nuovi eventi senza necessità di ricaricare il browser.
  - Cliccando sulla campanella si apre il pannello laterale (drawer) con la cronologia delle notifiche.
  - È possibile contrassegnare singole notifiche come lette oppure utilizzare l'azione massiva *Segna tutte come lette*.
- **Tipologie di Notifiche Registrate**:
  - Notifiche informative di avvenuta compressione automatica delle immagini con dettaglio sul risparmio di peso e percentuale.
  - Eventuali comunicazioni di sistema e aggiornamenti operativi.
