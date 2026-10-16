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

---

## 📝 Regola di Aggiornamento
Ogni risoluzione di bug che introduce una modifica a comportamenti critici o risolve un'anomalia complessa deve essere aggiunta a questa pagina per conservare la memoria storica del team.
