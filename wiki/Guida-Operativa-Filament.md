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
- Elenco delle prenotazioni ricevute con filtri per evento, data e stato pagamento.
- Visualizzazione del dettaglio partecipanti (standard vs FAI) e quote incassate.
- Funzione di Check-in rapido per gli addetti all'accoglienza in loco.

### 4. Risorsa Membri FAI (`FaiMemberResource`)
- Consultazione anagrafica soci e verifica validità tessere.

---

## 📝 Regola di Aggiornamento
Ogni modifica a form, tabelle o azioni personalizzate nel pannello Filament deve essere documentata in questa pagina.
