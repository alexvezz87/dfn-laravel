# 📜 Storico Modifiche & Interventi

Registro cronologico di tutte le modifiche, funzionalità rilasciate, correzioni di bug e refactoring eseguiti sul progetto.

---

| Data | Tipo | Rif. Issue | Titolo Intervento | Autore / Agente | PR / Note |
| :--- | :--- | :--- | :--- | :--- | :--- |
| 2026-10-21 | 🚨 Hotfix | #41 | Configurazione bucket GCS reale `dfn_staging` per persistenza media su Cloud Run | Alex / Antigravity | Collegamento bucket `dfn_staging` nel deploy workflow, volume mount Cloud Storage FUSE su `/app/storage/app/public`, verifica bucket e FAQ |
| 2026-10-21 | ✨ Feature | #39 | Griglia Media 1:1 WordPress (tessere quadrate densità 2-10 cols) e persistenza GCS Volume Mounts su Cloud Run | Alex / Antigravity | Vista blade `media-grid-card`, densità fino a 10 colonne, switcher icone compatto, volume mount FUSE su `/app/storage/app/public` in workflow CI/CD, test e wiki |
| 2026-10-21 | ✨ Feature | #37 | Visualizzazione a Griglia stile WordPress per Libreria Media con switcher e Slide-Over dettagli | Alex / Antigravity | Switcher Griglia/Elenco in `ListMedia`, `contentGrid` dinamico, card responsive con anteprima `h-44`, colonne adattive, `ViewAction` con cassetto slideOver, test PHPUnit e guida |
| 2026-10-21 | ⚡ Enhancement | #35 | Preservazione nomi file leggibili upload e refresh istantaneo form evento | Alex / Antigravity | Nome originale slugificato con anti-collisione su FileUpload, pre-compilazione titolo da nome file, `$this->refreshFormData()` post-salvataggio in `EditEvent`, test PHPUnit e documentazione wiki |
| 2026-10-21 | 🐛 Bug Fix | #33 | Fix URL anteprime media e FileUpload in Filament: percorsi storage root-relative e fix rotta 403 | Alex / Antigravity | Configurazione storage root-relative `/storage`, disattivazione `serve` su private, APP_URL in staging, regression test PHPUnit e FAQ |
| 2026-10-21 | 🔧 Chore | #31 | Risoluzione leak di sicurezza GitGuardian: rimozione APP_KEY da phpunit.xml e .env.testing | Alex / Antigravity | Rimozione chiavi statiche base64, generazione dinamica AES-256 in tests/TestCase.php, suite 40/40 test passata e aggiornamento FAQ |
| 2026-10-21 | ✨ Feature | #29 | Centro Notifiche Topbar, Libreria Media centralizzata (`MediaResource`) e riorganizzazione menu Impostazioni | Alex / Antigravity | Database notifications con campanella e polling 30s, modello `MediaItem`, risorsa `MediaResource` con eliminazione fisica storage, sidebar a 3 gruppi, test PHPUnit e wiki |
| 2026-10-21 | ⚡ Enhancement | #27 | Ottimizzazione automatica trasparente immagini: upload permissivo con resize e blocco rigido configurabile | Alex / Antigravity | Modalità trasparente con tetto server (50MB) e resize automatico, blocco rigido condizionato a `media_auto_compress`, trait `OptimizesEventImages` in `CreateEvent`/`EditEvent`, test e wiki |
| 2026-10-21 | ✨ Feature | #25 | Gestione Media & Upload in Filament, tetto Docker 50MB e sistema di compressione/ottimizzazione automatica immagini | Alex / Antigravity | Tabella `settings` e modello `Setting`, pagina Filament `ManageMediaSettings`, limiti dinamici `EventResource`, `OptimizeImageAction` con GD, test PHPUnit e wiki |
| 2026-10-20 | ✨ Feature | #23 | Funnel pubblico di prenotazione: selezione turno, form intestatario, verifica live tessere FAI e generazione biglietto | Alex / Antigravity | Migrazione DB `slot_id`/`booking_code`/`fai_cards`, `CreateBookingAction` atomica con lock capienza, endpoint `/api/check-fai-card`, pagina biglietto con QR `/prenotazione/{code}`, test e wiki |
| 2026-10-20 | 🔧 Chore | #21 | Allineamento governance AGENTS.md e setup bacheca tasks/ per pattern Orchestratore ed Esecutore | Alex / Antigravity | Allineamento Sezione 6 di AGENTS.md, esclusione bacheca tasks/ in .gitignore e salvaguardia .gitkeep |
| 2026-10-17 | 🔧 Chore | #19 | Rimozione dati sensibili di produzione Netsons e credenziali script (GitGuardian) | Alex / Antigravity | Eliminazione `netsons_prod_data.json` e `export_netsons_data.py`, inclusione `/storage/sync/` e `/scripts/` in `.gitignore` |
| 2026-10-17 | 🔧 Chore | #17 | Risoluzione leak di sicurezza credenziali database (GitGuardian) e configurazione GitHub Secrets | Alex / Antigravity | Rimozione password in chiaro da Dockerfile e deploy workflow, configurazione secret protetti su GitHub Actions |
| 2026-10-17 | ⚡ Enhancement | #15 | Tooltip popup modali esplicativi, layout picker visivo, rimozione campo WooCommerce e anteprima pubblica scheda evento | Alex / Antigravity | Tooltip modali con pulsanti `(?)`, layout picker con miniature scheletriche, rotta `/evento/{event}` e preview 1:1, rimozione `product_id`, test e wiki |
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
