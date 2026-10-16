# 📚 Wiki del Progetto DFN Laravel

Benvenuti nella Wiki ufficiale del progetto **DFN Laravel**, la piattaforma di gestione eventi, slot di accesso, prenotazioni e tesserati FAI.

---

## 🧭 Mappa della Documentazione

| Sezione | Descrizione |
| :--- | :--- |
| 🏗️ [Architettura e Database](./Architettura-e-Database.md) | Struttura dei modelli Eloquent, relazioni, migrazioni e schema DB. |
| ⚙️ [Funzionalità e Moduli](./Funzionalita-e-Moduli.md) | Logiche di business per Eventi, Slot, Prenotazioni e validazione FAI. |
| 🖥️ [Guida Operativa Filament](./Guida-Operativa-Filament.md) | Manuale per la gestione delle risorse nel pannello di amministrazione. |
| 🩺 [Troubleshooting e FAQ](./Troubleshooting-e-FAQ.md) | Risoluzione di problemi noti, casi limite e note per lo staging/deploy. |
| 📜 [Storico Modifiche](./Storico-Modifiche.md) | Registro cronologico di tutte le feature, bug fix e refactoring rilasciati. |

---

## 🛠️ Stack Tecnologico

- **Backend**: PHP 8.3 + Laravel 13
- **Admin Panel**: Filament v3
- **Frontend / Asset**: Vite + Tailwind CSS v4
- **Database**: PostgreSQL / SQLite (dev)
- **Code Style & Linting**: Laravel Pint
- **Testing**: PHPUnit
- **Infrastruttura & Deploy**: Docker su Google Cloud Run (staging gestito tramite GitHub Actions)

---

## 📌 Regole di Aggiornamento
Questa Wiki è vincolante e fa parte della **Definition of Done**: ogni nuovo sviluppo o bug fix deve aggiornare la pagina di competenza prima di considerare chiuso il task. Per le linee guida complete vedi [DEVELOPMENT.md](../DEVELOPMENT.md).
