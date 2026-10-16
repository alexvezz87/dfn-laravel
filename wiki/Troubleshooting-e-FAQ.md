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
*Quando un bug critico viene risolto, inserisci qui una breve scheda di riepilogo con:*
- **Descrizione del problema**:
- **Causa scatenante**:
- **Fix applicato & Regression Test**:
- **Issue GitHub di riferimento**:

---

## 📝 Regola di Aggiornamento
Ogni risoluzione di bug che introduce una modifica a comportamenti critici o risolve un'anomalia complessa deve essere aggiunta a questa pagina per conservare la memoria storica del team.
