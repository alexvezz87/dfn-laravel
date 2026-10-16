### 🔗 Collegamento Issue (Obbligatorio)
- Risolve / Chiude: Closes #<!-- inserire qui il numero di Issue, es. Closes #42 -->

---

### 📝 Descrizione dei Cambiamenti
*Sintesi di cosa è stato implementato, migliorato o corretto.*

### 🏷️ Tipologia di Intervento (Seleziona una)
- [ ] ✨ **Feature**: Nuova funzionalità o nuovo modulo da zero
- [ ] ⚡ **Enhancement**: Miglioramento o estensione di funzionalità esistente
- [ ] 🐛 **Bug Fix**: Risoluzione di anomalia o errore
- [ ] 🚨 **Hotfix**: Bug bloccante urgente in produzione
- [ ] ♻️ **Refactor**: Debito tecnico, ottimizzazione architetturale senza cambio di logica esterna
- [ ] 🔧 **Chore / Ops**: Manutenzione, dipendenze, Docker, CI/CD, configurazioni
- [ ] 💡 **PoC / Idea**: Studio di fattibilità o prototipo sperimentale

---

### 📖 Documentazione & Wiki
- [ ] Documentazione della Wiki aggiornata (modifiche apportate nella cartella `wiki/` o testo riportato sotto)
- [ ] Riga aggiunta in `wiki/Storico-Modifiche.md`
- *Note per la Wiki*: <!-- Riassunto delle modifiche da sincronizzare sulla Wiki GitHub -->

---

### ✅ Checklist di Qualità & Chiusura (Obbligatoria)
- [ ] Il branch rispetta la convenzione (`feature/#ID-...`, `enhance/#ID-...`, `fix/#ID-...`, `refactor/#ID-...`, `chore/#ID-...`)
- [ ] I commit referenziano l'Issue associata (`ref #ID` o `fixes #ID`)
- [ ] I test automatizzati (Feature o Unit) sono presenti e passano tutti con successo (`php artisan test`)
- [ ] Il linter `vendor/bin/pint --dirty` è stato eseguito senza errori
- [ ] Nessun file temporaneo o istruzione di debug rimasta nel codice (`dd()`, `dump()`, `console.log`)
- [ ] Se applicabile, il frontend compila correttamente (`npm run build`)
- [ ] Le migrazioni DB hanno il metodo `down()` testato per il rollback
