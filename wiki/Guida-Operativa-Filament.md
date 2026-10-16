# 🖥️ Guida Operativa Filament Admin

Questa guida è destinata agli operatori di backoffice e agli sviluppatori che gestiscono il pannello amministrativo **Filament 3**.

---

## 🔑 Accesso al Pannello
- URL: `/admin`
- Requisiti: account utente con credenziali autorizzate ad accedere al pannello.

---

## 📋 Risorse Gestite

### 1. Risorsa Eventi (`EventResource`)
- **Creazione / Modifica**: consente di impostare date evento, orari, capienze e tariffe standard e FAI.
- **Stato Evento**: gestione del ciclo di vita (`draft`, `published`, `archived`).
- **Apertura Prenotazioni**: data a partire dalla quale il pubblico può prenotare.

### 2. Risorsa Slot (`SlotResource`)
- Visualizzazione e gestione dei turni orari per ciascun evento.
- Monitoraggio della capienza e dei posti residui per ogni turno.

### 3. Risorsa Prenotazioni (`BookingResource`)
- **Navigazione**: menu laterale *Gestione Eventi* > *Prenotazioni*.
- **Tabella di Gestione**:
  - Visualizzazione ID univoco, Evento collegato, Intestatario con email di contatto.
  - Conteggi partecipanti differenziati: Totale Partecipanti (`primary`), Quota Intera (`gray`) e Soci FAI (`warning`).
  - Importo totale dovuto (€) e badge semantico per lo stato:
    - 🟢 `Confermata` (`confirmed`)
    - 🟠 `In attesa FAI` (`pending_approval`): richiede approvazione manuale della tessera.
    - 🔵 `In attesa pag.` (`pending_payment`)
    - 🔴 `Annullata` (`cancelled`)
  - Colonna Check-in con data/ora effettiva o stato "Non effettuato".
- **Filtri Disponibili**:
  - Filtro per Evento specifico.
  - Filtro per Stato prenotazione.
  - Filtro ternario Check-in: *Tutti* / *Effettuato* / *Da effettuare*.
- **Azioni Rapide di Riga**:
  - 🎟️ **Check-in**: per le prenotazioni confermate non ancora entrate, registra l'ingresso con data/ora corrente in 1 clic.
  - ↩️ **Annulla Check-in**: per correggere ingressi registrati per errore.
  - 🛡️ **Approva FAI**: visibile per le prenotazioni in stato `pending_approval`, convalida la tessera FAI e porta lo stato a `confirmed`.
- **Scheda di Dettaglio / Modifica**:
  - Sezioni organizzate: Dati Intestatario, Partecipanti & Biglietti, Contabilità e Note Interne.

### 4. Risorsa Membri FAI (`FaiMemberResource`)
- Consultazione anagrafica soci e verifica validità tessere.

---

## 📝 Regola di Aggiornamento
Ogni modifica a form, tabelle o azioni personalizzate nel pannello Filament deve essere documentata in questa pagina.
