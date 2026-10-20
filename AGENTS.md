# 🤖 Istruzioni e Regole Operative per Agenti AI

> **DIRETTIVA PRIMARIA PER L'AGENTE**: Prima di eseguire qualsiasi operazione, creazione file o modifica di codice, **DEVI** leggere e conformarti rigorosamente alle regole definite in [DEVELOPMENT.md](./DEVELOPMENT.md).

---

## 📌 1. Tracciamento Obbligatorio su GitHub (Nessun Intervento Anonimo)

Ogni richiesta dell'utente DEVE essere tracciata su GitHub all'interno della tassonomia a 6 categorie ufficiali.

1. **Prima di Scrivere Codice**:
   - Chiedi all'utente o verifica se esiste già una GitHub Issue aperta per il task in corso (`gh issue list`).
   - Se l'Issue non esiste, proponi all'utente di crearla o creala usando `gh issue create` con l'apposito template:
     - 💡 `type: idea` per proposte e PoC
     - ✨ `type: feature` per nuovi moduli/funzionalità da zero
     - ⚡ `type: enhancement` per miglioramenti di funzionalità esistenti
     - 🐛 `type: bug` per segnalazione e risoluzione bug (o `priority: critical` per 🚨 hotfix)
     - ♻️ `type: refactor` per debito tecnico e riorganizzazione codice
     - 🔧 `type: chore` per manutenzione tecnica, dipendenze o CI/CD
2. **Branch di Lavoro Conforme**:
   - Ogni modifica deve avvenire su un branch dedicato:  
     `feature/#<id>-nome`, `enhance/#<id>-nome`, `fix/#<id>-nome`, `hotfix/#<id>-nome`, `refactor/#<id>-nome`, `chore/#<id>-nome` o `idea/#<id>-nome`.
3. **Commit Messaggi Tracciati (Conventional Commits)**:
   - Includi sempre il prefisso e il riferimento: `feat(...): ... (ref #<id>)`, `enhance(...): ... (ref #<id>)`, `fix(...): ... (fixes #<id>)`, `refactor(...): ... (ref #<id>)`, `chore(...): ... (ref #<id>)`.

---

## 📌 2. Aggiornamento Obbligatorio della Wiki di Progetto

L'attività di sviluppo **NON è conclusa** finché la documentazione non è allineata:
1. **Aggiorna i file della cartella `wiki/`**:
   - Se hai aggiunto una **Feature** o un **Enhancement**: aggiorna `wiki/Funzionalita-e-Moduli.md` e `wiki/Guida-Operativa-Filament.md`.
   - Se hai modificato il **Database**: aggiorna `wiki/Architettura-e-Database.md`.
   - Se hai risolto un **Bug critico** o un caso limite: documentalo in `wiki/Troubleshooting-e-FAQ.md`.
   - Se hai eseguito un **Refactoring architetturale**: aggiorna `wiki/Architettura-e-Database.md`.
   - Aggiungi **sempre** una riga di riepilogo in `wiki/Storico-Modifiche.md`.
2. **Report Finale**:
   - Nel tuo messaggio di chiusura, includi il testo aggiornato destinato alla Wiki di GitHub, oltre alla descrizione per la Pull Request (`Closes #<id>`).

---

## 📌 3. Standard Tecnici e Architetturali

- **Stack Tecnologico**: PHP 8.3, Laravel 13, Filament 3, PHPUnit, Laravel Pint, Tailwind CSS v4, Vite.
- **PHP 8 Strict**:
  - Dichiara sempre i return type e i tipi dei parametri su tutti i metodi.
  - Usa Constructor Property Promotion.
  - Graffe `{}` sempre obbligatorie per tutte le strutture di controllo (`if`, `foreach`, etc.).
- **Laravel Idiomatico**:
  - Genera file preferibilmente tramite `php artisan make:... --no-interaction`.
  - Non inserire logiche complesse nei controller: usa `FormRequest`, `Service`/`Action` classes e `JsonResource`.
  - Rispetta le convenzioni di Filament 3 per schemi di form, tabelle e azioni.
- **Database & Migrazioni**:
  - Metodo `down()` obbligatorio e testato per garantire la reversibilità.
  - Mai modificare migrazioni già esistenti nella cronologia: crea nuove migrazioni incrementali.

---

## 📌 4. Qualità del Codice & Testing Obbligatorio

- **Regression Test per ogni Bug Fix**:
  - Non considerare risolto un bug senza aver prima scritto un test automatizzato che lo riproduceva e che ora passa.
- **Testing**:
  - Esegui i test mirati prima di completare il task: `php artisan test --filter=NomeTest`.
- **Formattazione con Laravel Pint**:
  - Prima di dichiarare concluso il lavoro su file PHP, esegui sempre:
    ```bash
    vendor/bin/pint --dirty
    ```
- **Pulizia Debug**:
  - Nessuna chiamata a `dd()`, `dump()`, `ray()` o `console.log` deve rimanere nel codice finale.

---

## 📌 5. Riferimento Completo
Per tutti i dettagli, esempi di codice, la matrice di aggiornamento Wiki e la checklist completa di chiusura:
👉 **[DEVELOPMENT.md](./DEVELOPMENT.md)**
---

## 📌 6. Flusso Orchestratore ("Cervello") & Esecutore ("Braccio")

Il repository adotta il pattern di orchestrazione a due livelli: un *"Cervello"* analitico (sessione centrale ad alto ragionamento con visione globale su Bedrock e Laravel) e un *"Braccio"* esecutivo (istanza IDE locale focalizzata su Laravel).

1. **Bacheca Incarichi (`tasks/`)**:
   - I compiti dettagliati vengono depositati dal Cervello nella cartella locale `tasks/` (es. `tasks/task-<nome-lavoro>.md`), esclusa da Git via `.gitignore`.
2. **Comportamento dell'Agente Locale ("Braccio")**:
   - All'avvio di un task indicato in `tasks/`, l'agente legge immediatamente il file markdown specificato per comprendere contesto, Issue ID, branch da creare e requisiti tecnici.
   - Rispetta la tassonomia ufficiale, applica `vendor/bin/pint --dirty`, esegue i test `php artisan test` e aggiorna la documentazione in `wiki/`.
3. **Resoconto Finale in Calce al Task**:
   - Al completamento dell'attività, l'agente appende in fondo al file `tasks/task-*.md`:
     ```markdown
     ## 📋 Esito Esecuzione (Braccio)
     - **Stato**: Completato con successo / Bloccato
     - **Issue & Branch**: `#<id>` su `<tipo>/#<id>-<nome>`
     - **Commit**: `<tipo>(<ambito>): ... (ref #<id>)`
     - **Test & Pint**: Esito `php artisan test` e `vendor/bin/pint`
     - **Wiki Aggiornata**: Pagine modificate in `wiki/`
     - **Note per il Cervello**: Eventuali dubbi architetturali o casi limite scoperti
     ```
