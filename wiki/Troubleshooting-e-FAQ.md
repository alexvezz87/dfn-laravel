# 🩺 Troubleshooting e FAQ

Questa pagina raccoglie la risoluzione di problemi noti, anomalie risolte (bug fix storici) e procedure di manutenzione per ambienti di sviluppo, staging e produzione.

---

## 🔍 Problemi Noti e Soluzioni

### 1. Errori nei Manifest / Asset Vite
- **Sintomo**: Errore `Unable to locate file in Vite manifest`.
- **Causa**: Mancata compilazione degli asset frontend dopo modifiche a Blade, CSS o JS.
- **Soluzione**: Esegui `npm run build` o avvia il dev server con `npm run dev`.

### 2. Violazioni di Stile o Linting fallito
- **Sintomo**: Modifiche a file PHP non uniformi alle convenzioni di progetto.
- **Soluzione**: Esegui `vendor/bin/pint --dirty` prima di committare.

### 3. Migrazioni non allineate o Rollback
- **Sintomo**: Disallineamento schema DB tra branch o ambienti.
- **Soluzione**:
  ```bash
  php artisan migrate:status
  php artisan migrate
  ```

---

## 🐛 Registro Bug Storici e Casi Limite

### 1. Errore 500 Server Error nel tab "Eventi Conclusi & Passati" (`/admin/events?activeTab=past`)
- **Descrizione del problema**: Aprendo la vista degli eventi passati si verificava un crash 500 su Cloud Run / Staging.
- **Causa scatenante**: In `app/Filament/Resources/EventResource.php` alla riga 252, il calcolo di fallback della capacità eseguiva `$record->slots()->sum('capacity')` anziché `$record->slots()->sum('max_capacity')`. Quando un evento passato ha `total_capacity` nullo o zero, veniva scatenata una query su colonna inesistente: `SQLSTATE[42S22]: Unknown column 'capacity'`.
- **Fix applicato & Regression Test**: Corretto il nome della colonna in `max_capacity` ed esteso `test_can_list_upcoming_and_past_events` in `tests/Feature/Filament/EventResourceTest.php` con evento passato e slot associato.
- **Issue GitHub di riferimento**: [#11](https://github.com/alexvezz87/dfn-laravel/issues/11)

### 2. Alert di Sicurezza GitGuardian: Credenziali Database in chiaro (MySQL Assignment)
- **Descrizione del problema**: GitGuardian ha intercettato una credenziale MySQL esposta (`DB_PASSWORD`) nel repository GitHub.
- **Causa scatenante**: Nel `Dockerfile` e nel workflow di deployment `deploy-staging.yml` era stato inserito un valore di fallback in chiaro per la password di TiDB Cloud in assenza dei GitHub Secrets configurati.
- **Fix applicato & Best Practice**: 
  1. Rimosse tutte le credenziali e password in chiaro da `Dockerfile` e `.github/workflows/deploy-staging.yml`.
  2. Registrati i secret cifrati in GitHub Actions (`STAGING_DB_PASSWORD`, `STAGING_DB_HOST`, `STAGING_DB_PORT`, `STAGING_DB_DATABASE`, `STAGING_DB_USERNAME`).
  3. Procedura di rotazione della password su TiDB Cloud e chiusura alert su GitGuardian.
- **Issue GitHub di riferimento**: [#17](https://github.com/alexvezz87/dfn-laravel/issues/17)

### 3. Alert di Sicurezza GitGuardian: Dati Personali e Credenziali FTP (Netsons)
- **Descrizione del problema**: GitGuardian ha segnalato "Company Email Password" su `scripts/export_netsons_data.py` e multiple occorrenze "Generic High Entropy Secret" su `storage/sync/netsons_prod_data.json`.
- **Causa scatenante**: Lo script di migrazione conteneva in chiaro la password dell'utente FTP di Netsons (`upload@dfnprenotazioni.it`), mentre il file JSON di dump conteneva i `qr_token` casuali a 32 caratteri delle 155 prenotazioni storiche e i dati dei clienti.
- **Fix applicato & Best Practice**:
  1. Rimossi completamente dal tracking Git `scripts/export_netsons_data.py` e `storage/sync/netsons_prod_data.json`.
  2. Inseriti `/storage/sync/` e `/scripts/` in `.gitignore` per evitare qualsiasi leak accidentale futuro.
  3. Procedura di rotazione della password FTP su cPanel Netsons e chiusura degli alert sulla dashboard GitGuardian.
- **Issue GitHub di riferimento**: [#19](https://github.com/alexvezz87/dfn-laravel/issues/19)

### 4. Alert di Sicurezza GitGuardian: Laravel APP_KEY nei file di configurazione test (`phpunit.xml` e `.env.testing`)
- **Descrizione del problema**: GitGuardian ha segnalato un alert di severità alta rilevando il pattern di una `Laravel APP_KEY` in `phpunit.xml` e `.env.testing`.
- **Causa scatenante**: Per consentire l'esecuzione automatizzata dei test PHPUnit (sessioni, crittografia cookie e notifiche) senza generare eccezioni `No application encryption key has been specified`, erano state inserite stringhe statiche `base64:...` nei file di test versionati su Git. I crawler di GitGuardian rilevano qualsiasi hash a 44 caratteri base64 assegnato a variabili nominate `APP_KEY`.
- **Fix applicato & Best Practice**:
  1. Rimosso `<env name="APP_KEY" .../>` da `phpunit.xml`.
  2. Rimosso `APP_KEY=...` da `.env.testing`.
  3. Inserito il fallback dinamico in memoria dentro `tests/TestCase.php::createApplication()`:
     ```php
     if (empty(config('app.key'))) {
         config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
     }
     ```
  4. Nessun segreto o hash statico rimane presente nei file tracciati, mentre la suite di test dispone sempre di una chiave di crittografia a 256-bit valida a runtime.
  5. Chiusura dei due alert sulla dashboard GitGuardian selezionando **"Resolved: Test key / False positive"** o **"Revoked"**.
- **Issue GitHub di riferimento**: [#31](https://github.com/alexvezz87/dfn-laravel/issues/31)

### 5. Anteprime Media Rotte o Blocco "Waiting for size" su FileUpload (Mixed Content & APP_URL)
- **Descrizione del problema**: Nella Libreria Media l'anteprima risultava rotta, mentre nel form di modifica evento il campo FileUpload restava bloccato su "Waiting for size" / spinner infinito. Nel front-end pubblico invece la locandina era visibile.
- **Causa scatenante**: `Storage::disk('public')->url(...)` utilizzava il default `http://localhost/storage/...` in assenza di `APP_URL` in Staging. Il browser su connessione HTTPS bloccava il fetch per Mixed Content. Inoltre, `'serve' => true` sul disco `'local'` privato generava un conflitto con codice di risposta 403 Forbidden su `/storage/{path}`.
- **Fix applicato & Best Practice**:
  1. Disattivato `'serve' => false` su disco `'local'`.
  2. Impostato `'url' => env('FILESYSTEM_PUBLIC_URL', '/storage')` su disco `'public'` per generare percorsi root-relative conformi a qualunque protocollo/host.
  3. Configurato `APP_URL=https://dfn-laravel-staging.dfnprenotazioni.it` nel workflow di deploy Cloud Run.
- **Issue GitHub di riferimento**: [#33](https://github.com/alexvezz87/dfn-laravel/issues/33)

### 6. Perdita delle Immagini e Locandine ad ogni nuovo Deploy su Cloud Run (Stateless Container & GCS FUSE)
- **Descrizione del problema**: Dopo ogni nuovo rilascio di codice (deploy su Cloud Run via GitHub Actions), le immagini e locandine precedentemente caricate dagli utenti o tramite eventi non risultavano più visibili su disco (`404 Not Found`).
- **Causa scatenante**: Cloud Run è un'infrastruttura serverless *stateless*: ogni nuova revisione del container genera un'istanza con un filesystem locale pulito ed effimero, azzerando la cartella locale `/app/storage/app/public`.
- **Fix applicato & Best Practice**:
  1. Utilizzo della funzionalità nativa di Cloud Run **Cloud Storage Volume Mounts** (basata su Cloud Storage FUSE e ambiente di esecuzione di seconda generazione `--execution-environment=gen2`).
  2. Nel workflow di deploy `.github/workflows/deploy-staging.yml`:
     - Verifica della presenza del bucket dedicato di staging: `gs://dfn_staging`.
     - Aggiunta delle flag di montaggio persistente:
       ```bash
       --execution-environment=gen2
       --add-volume=name=media-storage,type=cloud-storage,bucket=dfn_staging
       --add-volume-mount=volume=media-storage,mount-path=/app/storage/app/public
       ```
  3. L'entrypoint Docker esegue `php artisan storage:link --force`, creando il collegamento simbolico tra `/app/public/storage` e il bucket montato.
  4. **Bucket Effettivo Configurato**: `dfn_staging` (Region: `europe-west1`, Class: `Standard`, Accesso gestito dal Service Account Cloud Run).
- **Issue GitHub di riferimento**: [#39](https://github.com/alexvezz87/dfn-laravel/issues/39) e [#41](https://github.com/alexvezz87/dfn-laravel/issues/41)

---

## 📝 Regola di Aggiornamento
Ogni risoluzione di bug che introduce una modifica a comportamenti critici o risolve un'anomalia complessa deve essere aggiunta a questa pagina per conservare la memoria storica del team.

