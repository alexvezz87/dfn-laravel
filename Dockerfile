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

ENV COMPOSER_ALLOW_SUPERUSER=1

# Ottimizzazione della cache dei layer Docker per Composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist

# Copia l'intero codice sorgente del progetto
COPY . .

# Genera l'autoload ottimizzato per la produzione
RUN composer dump-autoload --optimize --no-dev

# Configura i permessi corretti su storage e bootstrap/cache per il web server
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Rispetta la variabile d'ambiente $PORT fornita dinamicamente da Google Cloud Run
CMD ["sh", "-c", "SERVER_NAME=:${PORT} frankenphp run --config /etc/caddy/Caddyfile"]
