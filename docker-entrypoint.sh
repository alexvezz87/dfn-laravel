#!/bin/sh
set -e

# Crea la cartella database se non esiste
mkdir -p /app/database

# Crea il file SQLite vuoto se non esiste
if [ ! -f /app/database/database.sqlite ]; then
    touch /app/database/database.sqlite
fi

# Configura i permessi per www-data su database, storage e bootstrap/cache
chown -R www-data:www-data /app/database /app/storage /app/bootstrap/cache
chmod -R 775 /app/database /app/storage /app/bootstrap/cache
chmod 664 /app/database/database.sqlite 2>/dev/null || true

# Esegue le migrazioni forzate di Laravel
php artisan migrate --force

# Se la variabile PORT è impostata da Cloud Run, configura SERVER_NAME per FrankenPHP
if [ -n "$PORT" ]; then
    export SERVER_NAME=":${PORT}"
fi

# Avvia il processo web (FrankenPHP)
exec "$@"
