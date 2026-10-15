#!/bin/sh
set -e

# Configura PORT (default 8080 per Cloud Run) e SERVER_NAME per FrankenPHP
export PORT="${PORT:-8080}"
export SERVER_NAME=":${PORT}"

# Crea le cartelle necessarie se non esistono
mkdir -p /app/database /app/storage /app/bootstrap/cache

# Crea il file SQLite vuoto se non esiste (utile se DB_CONNECTION=sqlite)
if [ ! -f /app/database/database.sqlite ]; then
    touch /app/database/database.sqlite
fi

# Configura i permessi per www-data su database, storage e bootstrap/cache
chown -R www-data:www-data /app/database /app/storage /app/bootstrap/cache 2>/dev/null || true
chmod -R 775 /app/database /app/storage /app/bootstrap/cache 2>/dev/null || true
chmod 664 /app/database/database.sqlite 2>/dev/null || true

# Assicura che APP_KEY sia presente per crittografia e sessioni
if [ -z "$APP_KEY" ]; then
    export APP_KEY="base64:t6OfB9iGaVxtLi1SNgQTvyh0fDlSc/ewpMkziSegE3Y="
fi

# Esecuzione asincrona/in background delle migrazioni per non bloccare lo startup probe di Cloud Run
(
    # Breve pausa per assicurare che FrankenPHP abbia effettuato il bind della porta
    sleep 2
    echo "[Entrypoint] Avvio migrazioni del database in background..."
    if php artisan migrate --force --no-interaction; then
        echo "[Entrypoint] Migrazioni del database completate con successo."
    else
        echo "[Entrypoint] ATTENZIONE: Migrazione non riuscita o database temporaneamente non disponibile. Il server web rimane attivo."
    fi
) &

echo "[Entrypoint] Avvio FrankenPHP in ascolto sulla porta ${PORT} (SERVER_NAME=${SERVER_NAME})..."

# Avvia il processo web (FrankenPHP) sostituendo la shell corrente
exec "$@"

