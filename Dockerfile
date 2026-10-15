FROM dunglas/frankenphp:latest-php8.3

# Installazione estensioni PHP necessarie per Laravel, MySQL e Filament
RUN install-php-extensions \
    pdo_mysql \
    bcmath \
    intl \
    zip \
    pcntl \
    opcache

# Copia Composer dall'immagine ufficiale
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Imposta la directory di lavoro dell'applicazione
WORKDIR /app

# Variabili d'ambiente di default per produzione e database SQLite
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=production \
    APP_DEBUG=false \
    APP_KEY=base64:t6OfB9iGaVxtLi1SNgQTvyh0fDlSc/ewpMkziSegE3Y= \
    PORT=8080 \
    SERVER_NAME=:8080 \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/app/database/database.sqlite

# Ottimizzazione della cache dei layer Docker per Composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist

# Copia l'intero codice sorgente del progetto
COPY . .

# Genera l'autoload ottimizzato per la produzione
RUN composer dump-autoload --optimize --no-dev

# Configura lo script di entrypoint per inizializzazione SQLite e migrazioni
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh && sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh

# Configura i permessi corretti su database, storage e bootstrap/cache per il web server
RUN mkdir -p database storage bootstrap/cache \
    && chown -R www-data:www-data database storage bootstrap/cache \
    && chmod -R 775 database storage bootstrap/cache

# Entrypoint per inizializzazione ambiente, permessi e avvio migrazioni in background
ENTRYPOINT ["docker-entrypoint.sh"]

# Avvia FrankenPHP in ascolto sulla porta definita da SERVER_NAME (impostata dall'entrypoint su $PORT di Cloud Run)
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
