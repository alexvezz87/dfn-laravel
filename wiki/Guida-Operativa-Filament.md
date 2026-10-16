# 🖥️ Guida Operativa Filament Admin

Questa guida è destinata agli operatori di backoffice e agli sviluppatori che gestiscono il pannello amministrativo **Filament 3** (replica evoluta della dashboard WordPress/Bedrock).

---

## 🔑 Accesso al Pannello
- URL: `/admin` (es. `https://dfn-laravel-staging.dfnprenotazioni.it/admin`)
- Requisiti: account utente con credenziali autorizzate ad accedere al pannello.

---

## 📋 Risorse Gestite

### 1. Risorsa Eventi (`EventResource`)
- **Tabellone a Tab (in stile Bedrock)**:
  - 🟢 **Eventi in Programma & In Corso**: mostra gli eventi con `event_date_end >= oggi` (ordinati in ordine cronologico crescente, il più vicino in cima), con badge dinamico del conteggio.
  - ⚪ **Eventi Conclusi & Passati**: raggruppa lo storico degli eventi terminati (`event_date_end < oggi`), ordinati dal più recente al più vecchio.
  - 📋 **Tutti gli Eventi**: panoramica complessiva di tutti i record a catalogo.
- **Colonne del Tabellone**:
  - **Evento**: titolo in grassetto con location e città mostrati come descrizione sottostante.
  - **Date & Orari**: intervallo data inizio/fine formattato in italiano (`d/m/Y`) e orari di accesso.
  - **Tipologia**: badge semantico per *Fasce Orarie* (`info`) o *Flusso Libero* (`primary`), con indicazione della modalità di allocazione (*Automatica* o *Self-selection*).
  - **Tariffe**: riepilogo rapido quota Intera/Standard e quota Soci FAI.
  - **Capacità**: conteggio posti occupati rispetto alla capienza massima configurata (`occupati / totale`).
  - **Stato**: badge per *Pubblicato* (verde), *Bozza* (grigio), *Archiviato* (ambra).
- **Azioni Rapide di Riga**:
  - 🎟️ **Prenotazioni**: link diretto alla lista prenotazioni prefiltrata per l'evento selezionato.
  - ✏️ **Modifica**: apertura della scheda di dettaglio/modifica dell'evento.

---

### 2. Risorsa Prenotazioni (`BookingResource`)
- **Navigazione**: menu laterale *Gestione Eventi* > *Prenotazioni*.
- **Riepilogo Statistico (KPI Cards)** in cima alla vista:
  - 🎟️ **Prenotazioni Attive**: totale delle prenotazioni confermate nel sistema.
  - 👥 **Posti Occupati**: somma complessiva dei partecipanti confermati.
  - 📊 **Tasso di Occupazione**: percentuale calcolata sulla capienza totale prevista per gli eventi attivi.
  - ⚠️ **Tessere FAI da Verificare**: conteggio delle richieste in attesa con evidenziazione in giallo/ambra.
- **Navigazione a Tab (come in Bedrock `dfn-slot-manager`)**:
  - 🟢 **Prenotazioni Attive & Confermate**: mostra le prenotazioni valide (confermate e in attesa approvazione FAI).
  - ⚠️ **Verifica FAI**: filtro rapido dedicato esclusivamente alle prenotazioni con tessere FAI da convalidare (`pending_approval`).
  - ⏱️ **Storico Annullate & Tentativi**: visualizza i movimenti incompleti, falliti o annullati.
  - 📋 **Tutte le Prenotazioni**: visualizzazione globale.
- **Colonne della Tabella**:
  - **#ID**: badge identificativo della prenotazione.
  - **Evento**: titolo dell'evento e orario del turno/slot.
  - **Cliente**: nome in evidenza, email e recapito telefonico formattato.
  - **Tipologia**: badge arancione in evidenza `SOCIO FAI` se la prenotazione include soci FAI, altrimenti `Standard`.
  - **Partecipanti**: totale posti con breakdown dei biglietti (es. `2 (1 Std, 1 FAI)`).
  - **Importo**: importo totale dovuto con indicazione del metodo di pagamento.
  - **Stato**: badge semantico (*Confermata*, *Verifica FAI*, *In attesa pag.*, *Annullata*).
  - **Check-in**: badge *Effettuato* con data e ora o *Da effettuare*.
- **Azioni di Riga**:
  - 🎟️ **Check-in**: registra l'ingresso istantaneo per il visitatore.
  - ↩️ **Annulla Check-in**: ripristina lo stato da effettuare in caso di errore.
  - 🛡️ **Approva FAI**: convalida la tessera FAI e conferma la prenotazione.

---

### 3. Risorsa Slot (`SlotResource`)
- Visualizzazione e gestione dei singoli turni orari per ciascun evento.
- Monitoraggio della capienza e dei posti residui per ogni turno.

---

### 4. Risorsa Membri FAI (`FaiMemberResource`)
- Consultazione anagrafica soci e verifica validità tessere.
