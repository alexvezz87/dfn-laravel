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

- **Form di Creazione / Modifica Evento (Layout a 2 Colonne stile Bedrock)**:
  - **Colonna Sinistra (Dettagli & Logistica)**:
    - *Titolo Evento & Associazione Prodotto*: titolo evento sincronizzato con prodotto WooCommerce/Stripe e ID di riferimento.
    - *Logistica & Orari*: date inizio/fine, orari apertura/chiusura, data di apertura prenotazioni con countdown, location e città, descrizione evento con RichEditor.
    - *Tipologia Accesso, Capienza & Tariffe*: scelta tra Flusso Libero e Fasce Orarie, modalità di allocazione (automatica o self-selection), capienza massima complessiva, quota Standard (€) e quota agevolata Socio FAI (€).
  - **Colonna Destra (Pubblica & Workflow)**:
    - *Stato Evento*: Pubblicato (Attivo), Bozza, Archiviato.
    - *Workflow Approvazione*: Conferma automatica o Approvazione manuale tessere FAI.
    - *Stato Modalità Prenotazione*: Aperte (Form Online), Chiuse / Sold Out, In Arrivo.
    - *Modalità di Pagamento*: Solo Online (Stripe), Solo in Loco, o Entrambe.
    - *Annullamento Automatico*: timeout in ore per cancellare ordini non pagati.
    - *Modalità Evento di Test*: isolamento notifiche su email di collaudo dedicata.

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
