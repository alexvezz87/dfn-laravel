---
name: "♻️ Refactoring / Debito Tecnico"
about: "Proponi una pulizia, riorganizzazione architetturale o ottimizzazione interna del codice"
title: "[REFACTOR] "
labels: ["type: refactor"]
assignees: ""
---

### 🧹 Obiettivo del Refactoring
*Spiega quale parte del codice necessita di essere riordinata e perché (es. codice duplicato, classi troppo grandi, debito tecnico, query N+1).*

### 📐 Piano di Intervento
- [ ] Estrazione di Service / Action class
- [ ] Semplificazione controller o form requests
- [ ] Pulizia o tipizzazione metodi

### ⚠️ Regola Fondamentale di Sicurezza
- [ ] **Nessun cambio di comportamento esterno**: Le API e i flussi utente devono continuare a funzionare esattamente come prima.
- [ ] Tutta la suite di test esistente (`php artisan test`) deve passare al 100% prima e dopo il refactoring.
