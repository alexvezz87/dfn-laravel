# 📜 Storico Modifiche & Interventi

Registro cronologico di tutte le modifiche, funzionalità rilasciate, correzioni di bug e refactoring eseguiti sul progetto.

---

| Data | Tipo | Rif. Issue | Titolo Intervento | Autore / Agente | PR / Note |
| :--- | :--- | :--- | :--- | :--- | :--- |
| 2026-10-17 | ⚡ Enhancement | #13 | Parità completa Form Modifica Evento e stile FAI con WordPress Bedrock | Alex / Antigravity | Allineamento 100% schema form (`EventResource`), 7 box sidebar, media upload, layout selector, duplicazione evento, CSS FAI (`dfn-admin-custom.css`), test e wiki |
| 2026-10-17 | 🐛 Bug Fix | #11 | Fix errore 500 su tab Eventi Conclusi & Passati (query colonna `max_capacity` su slot) | Alex / Antigravity | Correzione query capacità slot in `EventResource`, regression test PHPUnit e doc FAQ |
| 2026-10-17 | ⚡ Enhancement | #9 | Allineamento Form Evento allo Screenshot 4 e implementazione anagrafica Soci FAI (FaiMemberResource) | Alex / Antigravity | Form Evento a 2 colonne, anagrafica Soci FAI con ricerca e filtri validità, test e guida |
| 2026-10-17 | ⚡ Enhancement | #7 | Import dati reali Netsons (155 prenotazioni, 2 eventi) e vista ManageEventBookings per singolo evento 1:1 con Bedrock | Alex / Antigravity | Comando `dfn:sync-netsons`, pagina `ManageEventBookings`, progress bar slot e navigazione FAI Prenotazioni |
| 2026-10-16 | ⚡ Enhancement | #5 | Replica dashboard Bedrock (KPI card, tab prenotazioni/eventi, badge FAI) e connessione Cloud Run TiDB | Alex / Antigravity | BookingStatsOverviewWidget, tab su ListBookings e ListEvents, fix sicurezza APP_KEY GitGuardian |
| 2026-10-16 | ✨ Feature | #3 | Implementazione BookingResource e gestione prenotazioni in Filament | Alex / Antigravity | Tabella, filtri, azioni check-in/approva FAI, test e guida |
| 2026-10-16 | 🔧 Chore | #1 | Setup regole di sviluppo, linee guida AI, template GitHub e Wiki di progetto | Alex / Antigravity | `DEVELOPMENT.md`, `AGENTS.md`, template GitHub e cartella `wiki/` |

---

## 📌 Guida alla Compilazione
Per ogni nuovo sviluppo o bug fix completato:
1. Aggiungi una riga in cima alla tabella con:
   - **Data**: Formato `YYYY-MM-DD`.
   - **Tipo**: ✨ Feature / ⚡ Enhancement / 🐛 Bug Fix / 🚨 Hotfix / ♻️ Refactor / 🔧 Chore / 💡 PoC.
   - **Rif. Issue**: Link o ID della Issue GitHub (es. `#42`).
   - **Titolo Intervento**: Breve sintesi dell'operazione.
   - **Autore / Agente**: Nome dello sviluppatore o AI Agent.
   - **PR / Note**: Numero di Pull Request o link alla documentazione aggiornata.
