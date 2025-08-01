FROM php:8.3-fpm-alpine

# Aggiungi le dipendenze di sistema necessarie (Alpine è una distribuzione molto leggera)
RUN apk update && apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    libzip-dev \
    git \
    bash \
    nginx \
    curl \
    icu-dev \
    libxml2-dev \
    libxslt-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql bcmath intl soap xsl zip

# Installa Composer
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

# Crea la cartella per il progetto Laravel
WORKDIR /var/www

COPY . .

# Imposta i permessi per Laravel
RUN chown -R www-data:www-data /var/www && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Installa le dipendenze di Composer
RUN composer install

# Copia i file di Laravel (Assumendo che siano già nella directory corrente sul tuo host)

# Installa Node.js e npm
# RUN apk add --no-cache nodejs npm

# RUN npm install
# RUN npm run build
