# 🤖 Regole Obbligatorie di Sviluppo per Agenti e Assistenti AI

> **IMPORTANTE PER L'AGENTE AI**: Questo documento stabilisce le regole **tassative e vincolanti** da rispettare durante qualsiasi attività di sviluppo all'interno di questo repository.  
> Include gli standard tecnici (Laravel, PHP 8.3, Pint, PHPUnit), la **tassonomia ufficiale a 6 categorie per il tracciamento su GitHub** (Issue, Branch, Commit, PR) e l'**obbligo tassativo di aggiornamento della Wiki**, garantendo uno storico completo e una documentazione sempre allineata.

---

## 📌 Indice
1. [Principi Generali & Tracciabilità Obbligatoria](#1-principi-generali--tracciabilità-obbligatoria)
2. [Tassonomia degli Sviluppi: Le 6 Categorie Ufficiali](#2-tassonomia-degli-sviluppi-le-6-categorie-ufficiali)
3. [Ciclo di Tracciamento su GitHub (To Do -> In Progress -> Done)](#3-ciclo-di-tracciamento-su-github-to-do---in-progress---done)
4. [Aggiornamento Obbligatorio della Wiki di Progetto](#4-aggiornamento-obbligatorio-della-wiki-di-progetto)
5. [Convenzioni Git: Branch e Commit Tracciati](#5-convenzioni-git-branch-e-commit-tracciati)
6. [Pull Request & Chiusura Interventi](#6-pull-request--chiusura-interventi)
7. [Fase di Analisi Pre-Sviluppo](#7-fase-di-analisi-pre-sviluppo)
8. [Standard di Codice (PHP 8.3 & Laravel)](#8-standard-di-codice-php-83--laravel)
9. [Workflow Specifico per Bug Fix & Hotfix](#9-workflow-specifico-per-bug-fix--hotfix)
10. [Workflow Specifico per Nuove Feature & Enhancement](#10-workflow-specifico-per-nuove-feature--enhancement)
11. [Workflow Specifico per Refactoring & Chore](#11-workflow-specifico-per-refactoring--chore)
12. [Database, Migrazioni e Modelli Eloquent](#12-database-migrazioni-e-modelli-eloquent)
13. [Testing Obbligatorio (PHPUnit)](#13-testing-obbligatorio-phpunit)
14. [Qualità e Formattazione (Laravel Pint)](#14-qualità-e-formattazione-laravel-pint)
15. [Sicurezza e Best Practice](#15-sicurezza-e-best-practice)
16. [Checklist Finale di Chiusura Task (Definition of Done)](#16-checklist-finale-di-chiusura-task-definition-of-done)

---

## 1. Principi Generali & Tracciabilità Obbligatoria

- **Zero Interventi Anonimi**: Nessuna riga di codice deve essere scritta senza un riferimento tracciato su GitHub (Issue ID).
- **Tracciamento a 3 Stati**:
  - 📋 **To Do (Cosa bisogna fare)**: Ogni idea, proposta, bug, feature, refactoring o chore deve avere una GitHub Issue aperta.
  - ⚙️ **In Progress (Cosa si sta facendo)**: Issue assegnata, branch dedicato collegato alla Issue, visibilità immediata dello stato d'avanzamento.
  - ✅ **Done (Cosa è stato fatto)**: Pull Request approvata e mergiata, chiusura automatica della Issue con report dettagliato, storico archiviato e **Wiki aggiornata**.
- **Documentazione Viva (Wiki)**: Il codice non è considerato completato se la documentazione funzionale e tecnica sulla Wiki non è aggiornata.
- **Do Things the Laravel Way**: Segui sempre le convenzioni native di Laravel e Filament.
- **Minimo Impatto (No Scope Creep)**: Tocca solo i file necessari per l'Issue in corso. Non rifattorizzare codice non correlato senza aver aperto un'Issue di tipo `refactor`.
- **Nessun codice non testato**: Ogni nuova logica o correzione deve essere accompagnata da test automatizzati.

---

## 2. Tassonomia degli Sviluppi: Le 6 Categorie Ufficiali

Per coprire il 100% delle casistiche senza zone grigie, ogni attività DEVE essere classificata in una di queste 6 categorie:

| Categoria | Label GitHub | Icona | Quando si usa | Finisce nel Changelog Utente? |
| :--- | :--- | :---: | :--- | :---: |
| **Idea / Proposal** | `type: idea` | 💡 | Idee, proposte e studi di fattibilità non ancora approvati | No (resta in discussione) |
| **Feature** | `type: feature` | ✨ | Nuovi moduli o nuove funzionalità da zero | **Sì** |
| **Enhancement** | `type: enhancement`| ⚡ | Miglioramenti, ottimizzazioni UX o estensioni di funzionalità esistenti | **Sì** |
| **Bug Fix** | `type: bug` | 🐛 | Risoluzione di anomalie, errori o regressioni | **Sì** |
| **Refactor** | `type: refactor` | ♻️ | Miglioramento architetturale, pulizia del codice senza alterare il comportamento | No (storico tecnico) |
| **Chore / Ops** | `type: chore` | 🔧 | Manutenzione tecnica, dipendenze (`composer`/`npm`), Docker, CI/CD, config | No (storico tecnico) |

> 🚨 **Modalità Speciale: `hotfix`**  
> Se un bug si manifesta in produzione ed è **bloccante**, assume priorità massima: etichetta aggiuntiva `priority: critical`, branch `hotfix/#<id>-...` e rilascio immediato.

---

## 3. Ciclo di Tracciamento su GitHub (To Do -> In Progress -> Done)

```
                  ┌─────────────────────────────────────────────────────────┐
                  │                 GITHUB ISSUE CREATA                     │
                  │  [idea] 💡 / [feature] ✨ / [enhancement] ⚡            │
                  │  [bug] 🐛 / [refactor] ♻️ / [chore] 🔧                 │
                  └──────────────────────────┬──────────────────────────────┘
                                             │
                                             ▼
                  ┌─────────────────────────────────────────────────────────┐
                  │                   IN PROGRESS                           │
                  │  Branch: <tipo>/#<id>-breve-descrizione                 │
                  └──────────────────────────┬──────────────────────────────┘
                                             │
                                             ▼
                  ┌─────────────────────────────────────────────────────────┐
                  │         SVILUPPO, TEST, LINTING & AGGIORNAMENTO WIKI    │
                  │  Commit: feat(...) o fix(...) con ref #<id>             │
                  │  Documentazione aggiornata nella cartella wiki/         │
                  └──────────────────────────┬──────────────────────────────┘
                                             │
                                             ▼
                  ┌─────────────────────────────────────────────────────────┐
                  │               PULL REQUEST & HISTORICAL LOG             │
                  │  PR con `Closes #<id>` -> Merge su `staging` -> Done    │
                  │  Riga aggiunta in wiki/Storico-Modifiche.md             │
                  └─────────────────────────────────────────────────────────┘
```

### Regole per ciascuna categoria:
1. 💡 **Idea (`type: idea`)**: Non si scrive codice definitivo. Se necessario un prototipo per validare la fattibilità, si apre un branch `idea/#<id>-nome`. Una volta approvata, l'Issue viene convertita in `feature`, `enhancement` o `refactor`.
2. ✨ **Feature (`type: feature`)**: Nuova funzionalità. Richiede issue dettagliata con acceptance criteria, branch `feature/#<id>-...`, test dedicati e aggiornamento pagine funzionali della Wiki.
3. ⚡ **Enhancement (`type: enhancement`)**: Modifica/estensione di cosa già presente. Branch `enhance/#<id>-...`, test aggiornati e Wiki aggiornata.
4. 🐛 **Bug Fix (`type: bug`)**: Risoluzione errore. Richiede test di regressione obbligatorio prima del fix e aggiornamento di `wiki/Troubleshooting-e-FAQ.md`.
5. ♻️ **Refactor (`type: refactor`)**: Riorganizzazione architetturale (es. estrazione Action/Service, eliminazione N+1). Tutti i test preesistenti devono continuare a passare al 100%. Branch `refactor/#<id>-...`.
6. 🔧 **Chore (`type: chore`)**: Manutenzione (es. upgrade pacchetti, modifiche pipeline o Docker). Branch `chore/#<id>-...`.

---

## 4. Aggiornamento Obbligatorio della Wiki di Progetto

La documentazione della Wiki è memorizzata nella cartella versionata `wiki/` del repository (che funge da *Single Source of Truth* sincronizzata con Git) e sulla Wiki di GitHub (`https://github.com/alexvezz87/dfn-laravel/wiki`).

### Struttura Standard della Wiki:
- 📖 [`wiki/Home.md`](./wiki/Home.md): Panoramica del progetto, stack tecnologico e mappa della documentazione.
- 🏗️ [`wiki/Architettura-e-Database.md`](./wiki/Architettura-e-Database.md): Modelli Eloquent, relazioni, migrazioni e schema del database.
- ⚙️ [`wiki/Funzionalita-e-Moduli.md`](./wiki/Funzionalita-e-Moduli.md): Dettaglio di ogni modulo funzionale (Eventi, Slot, Prenotazioni, Membri FAI, Prezzi, Validazioni).
- 🖥️ [`wiki/Guida-Operativa-Filament.md`](./wiki/Guida-Operativa-Filament.md): Istruzioni e manuale per l'utilizzo del pannello amministrativo Filament.
- 🩺 [`wiki/Troubleshooting-e-FAQ.md`](./wiki/Troubleshooting-e-FAQ.md): Casi limite, risoluzione di errori noti, comandi utili e note operative per staging/produzione.
- 📜 [`wiki/Storico-Modifiche.md`](./wiki/Storico-Modifiche.md): Registro cronologico cumulativo degli interventi eseguiti con riferimento alle Issue e alle PR.

### Matrice di Aggiornamento Wiki per Categoria:

| Categoria Intervento | Pagine Wiki da Aggiornare Obbligatoriamente |
| :--- | :--- |
| ✨ **Feature** | `Funzionalita-e-Moduli.md`, `Guida-Operativa-Filament.md` (se admin), `Architettura-e-Database.md` (se DB) |
| ⚡ **Enhancement** | `Funzionalita-e-Moduli.md`, `Guida-Operativa-Filament.md` |
| 🐛 **Bug Fix** | `Troubleshooting-e-FAQ.md` (se bug critico o edge case complesso) |
| ♻️ **Refactor** | `Architettura-e-Database.md` (se varia l'architettura dei servizi/modelli) |
| 🔧 **Chore** | `Troubleshooting-e-FAQ.md` (se cambiano comandi di build, Docker o variabili d'ambiente) |
| **TUTTE** | `wiki/Storico-Modifiche.md` (aggiunta riga riepilogativa obbligatoria) |

---

## 5. Convenzioni Git: Branch e Commit Tracciati

### Convenzione Nomi Branch (Obbligatoria)
Il prefisso del branch deve rispecchiare la categoria, seguito da `/#<issue-id>-` e dalla descrizione sintetica in kebab-case:

- `feature/#<issue-id>-<descrizione>` (es: `feature/#42-creazione-slot-ricorrenti`)
- `enhance/#<issue-id>-<descrizione>` (es: `enhance/#48-filtro-data-prenotazioni`)
- `fix/#<issue-id>-<descrizione>` (es: `fix/#43-overlap-orari-prenotazioni`)
- `hotfix/#<issue-id>-<descrizione>` (es: `hotfix/#50-blocco-checkin-fai`)
- `refactor/#<issue-id>-<descrizione>` (es: `refactor/#46-estrazione-booking-service`)
- `chore/#<issue-id>-<descrizione>` (es: `chore/#45-upgrade-filament-3-3`)
- `idea/#<issue-id>-<descrizione>` (es: `idea/#44-poc-notifiche-whatsapp`)

### Convenzione Messaggi di Commit (Conventional Commits con Issue Reference)
Ogni commit deve indicare il tipo di intervento e referenziare l'Issue associata:

```
<tipo>(<ambito>): <descrizione sintetica imperativa> (ref #<issue-id>)
```

I tipi consentiti sono:
- `feat`: per Feature
- `enhance`: per Enhancement
- `fix`: per Bug Fix e Hotfix
- `refactor`: per Refactoring
- `chore`: per Manutenzione tecnica e dipendenze
- `perf`: per Ottimizzazioni prestazionali
- `docs`: per Documentazione e Wiki
- `test`: per Test automatizzati

Esempi:
- `feat(booking): implementa validazione slot sovrapposti (ref #42)`
- `enhance(admin): aggiunge esportazione csv tabella prenotazioni (ref #48)`
- `fix(calendar): risolve mancata sincronizzazione timezone UTC (fixes #43)`
- `refactor(slot): separa logica calcolo capienze da controller (ref #46)`
- `chore(deps): aggiorna filament alla versione 3.3 (ref #45)`
- `docs(wiki): aggiorna manuale operativo filament per checkin (ref #42)`

---

## 6. Pull Request & Chiusura Interventi

Nessun push diretto sui rami principali (`staging`, `main`).

1. **Apertura PR**:
   - Titolo della PR: `[#<issue-id>] <Tipo>: <Descrizione sintetica>`  
     (es. `[#42] Feature: Creazione slot ricorrenti`).
   - Nel corpo della PR inserire la clausola di collegamento automatico:
     - `Closes #<issue-id>` (o `Fixes #<issue-id>` per i bug).
2. **Template PR compilato**:
   - Tipologia selezionata tra le 6 ufficiali.
   - Riepilogo modifiche.
   - Checklist di verifica (test eseguiti, Pint eseguito, migrazioni testate, **Wiki aggiornata**).
3. **Merge & Storico**:
   - Al momento del merge, GitHub chiude automaticamente l'Issue associata.
   - Viene verificata la presenza della nuova riga in `wiki/Storico-Modifiche.md`.

---

## 7. Fase di Analisi Pre-Sviluppo

Prima di modificare o creare qualsiasi file:
1. **Verifica il riferimento GitHub**:
   - Se l'utente non ha specificato l'ID dell'Issue, l'agente deve verificare le Issue esistenti tramite `gh issue list` o chiedere all'utente quale Issue fa da riferimento (oppure offrirsi di crearla con `gh issue create` specificando la categoria corretta).
2. **Esamina la Wiki e il contesto circostante**: Leggi la documentazione in `wiki/` e i file correlati per comprendere l'architettura.
3. **Verifica le versioni**: Progetto con **PHP 8.3**, **Laravel 13**, **Filament 3** e **Vite/Tailwind v4**.
4. **Ispeziona lo schema DB**: Prima di toccare modelli o query, controlla migrazioni e chiavi esistenti.

---

## 8. Standard di Codice (PHP 8.3 & Laravel)

- **Strict Typing & Type Hints**:
  - Dichiara sempre i tipi di ritorno per tutti i metodi e funzioni.
  - Dichiara sempre i type hint per tutti gli argomenti di funzioni e metodi.
  - Usa i tipi nativi di PHP 8 (union types `string|int`, nullable `?User`, etc.).
- **Constructor Property Promotion**:
  ```php
  public function __construct(
      public readonly BookingService $bookingService,
      public readonly Mailer $mailer,
  ) {}
  ```
- **Control Structures**:
  - Utilizza **sempre** le parentesi graffe `{}` per tutte le strutture di controllo (`if`, `else`, `foreach`, `while`), anche per blocchi a singola riga.
- **Generazione File**:
  - Genera classi, controller, modelli e migrazioni preferibilmente tramite comandi Artisan (es. `php artisan make:... --no-interaction`).
- **Nomenclatura**:
  - Metodi e variabili in `camelCase` descrittivi (es. `isAvailableForBooking()`).
  - Route nominate con notazione dot (es. `bookings.store`, `events.show`).
  - Enum keys in `TitleCase` (es. `Pending`, `Confirmed`, `Cancelled`).

---

## 9. Workflow Specifico per Bug Fix & Hotfix

1. **Comprensione e Isolamento della Root Cause**: Identifica con precisione il motivo del malfunzionamento prima di toccare il codice.
2. **Scrittura del Test di Regressione**:
   - Scrivi un Feature o Unit Test che riproduce fedelmente il bug.
   - Verifica che il test **fallisca** con il codice difettoso e **passi** dopo la modifica.
3. **Intervento Chirurgico**: Correggi solo ciò che causa il bug, preservando il comportamento atteso esistente.
4. **Riferimento GitHub & Wiki**:
   - Commit con `(fixes #<issue-id>)`.
   - Se il bug era rilevante o causato da presupposti errati, documentalo in `wiki/Troubleshooting-e-FAQ.md`.

---

## 10. Workflow Specifico per Nuove Feature & Enhancement

1. **Form Requests**: Non validare nei Controller. Crea classi `FormRequest` dedicate con regole di autorizzazione e messaggi specifici.
2. **Service / Action Classes**: Isola la logica di dominio complessa in classi Service o Action riutilizzabili.
3. **API Resources**: Utilizza `JsonResource` di Eloquent per trasformare le risposte API.
4. **Filament Resources**: Rispetta gli standard di Filament 3 per Form Schema, Table Columns/Filters e Actions.
5. **Factories e Seeders**: Per ogni nuovo modello Eloquent, crea o aggiorna la rispettiva `Factory` e uno `Seeder` con dati realistici.
6. **Frontend & Asset**: Verifica che gli asset compilino (`npm run build`).
7. **Documentazione Wiki**: Aggiorna `wiki/Funzionalita-e-Moduli.md` e `wiki/Guida-Operativa-Filament.md`.

---

## 11. Workflow Specifico per Refactoring & Chore

1. **Refactoring sicuro**:
   - Esegui l'intera suite di test prima del refactor: `php artisan test`.
   - Esegui il refactoring a piccoli passi incrementali.
   - Nessun test esistente deve rompersi: l'API e i contratti pubblici non devono cambiare a meno che espressamente richiesto.
2. **Chore & Aggiornamenti**:
   - Se aggiorni pacchetti (`composer update`), verifica la compatibilità di Filament e delle estensioni installate.
   - Verifica l'integrità del build Docker locale o degli script di deployment.

---

## 12. Database, Migrazioni e Modelli Eloquent

- **Migrazioni Reversibili**: Ogni migrazione deve implementare un metodo `down()` coerente per consentire il rollback.
- **Mai alterare migrazioni pregresse**: Non modificare migrazioni già presenti nella cronologia; crea una nuova migrazione incrementale.
- **Integrità & Performance**: Definisci policy di delete su foreign keys (es. `cascadeOnDelete()`) e indici appropriati.
- **Mass Assignment**: Definisci `$fillable` (o `$guarded`) sui modelli Eloquent.
- **Relazioni Eloquent**: Tipizza sempre il tipo di ritorno dei metodi di relazione (es. `public function slots(): HasMany`).
- **Allineamento Wiki**: Aggiorna `wiki/Architettura-e-Database.md` per qualsiasi variazione al modello dati.

---

## 13. Testing Obbligatorio (PHPUnit)

- **Tooling**: Il progetto utilizza **PHPUnit** (`vendor/bin/phpunit` o `php artisan test`).
- **Feature Test prioritari**: Verifica i flussi completi end-to-end.
- **Utilizzo delle Factory**: Istanzia modelli tramite Factory (`Model::factory()->create()`).
- **Esecuzione Mirata**:
  ```bash
  php artisan test --filter=NomeDelTest
  ```
- **Nessun test rotto**: La suite di test dell'area impattata deve passare al 100%.

---

## 14. Qualità e Formattazione (Laravel Pint)

- **Formattazione Obbligatoria**:
  - Prima di finalizzare qualsiasi commit su file PHP, esegui:
    ```bash
    vendor/bin/pint --dirty
    ```
- Nessun file deve contenere violazioni delle convenzioni di stile Laravel/PSR-12.

---

## 15. Sicurezza e Best Practice

- **Nessun dato sensibile hardcoded**: Usa sempre `config('services.nome.chiave')` e variabili `.env`.
- **Policy e Autorizzazioni**: Proteggi azioni e risorse con Policy Laravel o `Gate::authorize()`.
- **Prevenzione Vulnerabilità**: Usa query builder ed Eloquent con prepared statement. Esegui sempre l'escaping nelle viste Blade (`{{ $var }}`).
- **Pulizia Debug**: Rimuovi ogni residuo di `dd()`, `dump()`, `ray()`, `logger()` o `console.log`.

---

## 16. Checklist Finale di Chiusura Task (Definition of Done)

Prima di notificare all'utente che il task è stato completato, l'agente AI **DEVE** verificare internamente:

- [ ] **Issue GitHub associata**: Esiste un'Issue GitHub categorizzata correttamente tra le 6 ufficiali.
- [ ] **Branch dedicato**: Lavorato su branch con naming conforme (`feature/#ID-...`, `enhance/#ID-...`, `fix/#ID-...`, `refactor/#ID-...`, `chore/#ID-...`).
- [ ] **Funzionalità / Fix / Refactor completato**: Requisiti e criteri di accettazione della Issue soddisfatti.
- [ ] **Test scritti e passanti**: Test di regressione o feature test implementati ed eseguiti con successo.
- [ ] **Pint eseguito**: `vendor/bin/pint --dirty` eseguito senza errori residui.
- [ ] **Documentazione Wiki aggiornata**: Aggiornati i file pertinenti in `wiki/` in base alla matrice di aggiornamento.
- [ ] **Storico modifiche compilato**: Aggiunta la riga di riassunto in `wiki/Storico-Modifiche.md`.
- [ ] **Nessun residuo temporaneo**: File di debug e script temporanei rimossi.
- [ ] **Report Finale Formattato per GitHub & Wiki**: L'agente fornisce all'utente:
  1. Categoria e ID Issue di riferimento.
  2. Sintesi delle modifiche eseguite e file toccati.
  3. Esito dei test e di Pint.
  4. Testo per la Pull Request (`Closes #<id>`).
  5. Estratto aggiornato pronto per la Wiki di GitHub.
