# --- Stage 1: PHP Dependencies ---
FROM composer:2.8 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# --- Stage 2: Frontend Assets (Optional - remove if using CDN/Inertia SSR separately) ---
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json vite.config.js ./
COPY resources/ ./resources/
RUN npm install && npm run build

# --- Stage 3: Final Production Image ---
FROM php:8.4-fpm-alpine

# Install System Dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    icu-dev

# Install PHP Extensions
RUN docker-php-ext-install pdo_mysql bcmath gd zip opcache intl

# Set up PHP OPCache for high performance
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Set working directory
WORKDIR /app

# Copy Application code
COPY . .

# Copy built vendors and assets from previous stages
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

# Directory Permissions (Forge usually does this via sudo)
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Configuration Files
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Cloud Run expects traffic on $PORT (Default 8080)
EXPOSE 8080

# Configure Environment for Cloud Run
ENV LOG_CHANNEL=stderr
ENV LOG_STDERR_FORMATTER="Monolog\Formatter\JsonFormatter"

# Use Entrypoint to handle runtime caching
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Start Supervisor to manage both Nginx and PHP-FPM
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]