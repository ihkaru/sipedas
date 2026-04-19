# Stage 1: Build PHP dependencies
FROM composer:latest AS composer-builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-scripts --no-progress --ignore-platform-reqs

# Stage 2: Build frontend assets
FROM node:20-slim AS node-builder
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm install
COPY . .
COPY --from=composer-builder /app/vendor ./vendor
RUN npm run build

# Stage 3: PHP Base Runtime (Lean & High Performance)
FROM dunglas/frankenphp:latest-alpine AS base
WORKDIR /app

# Core FrankenPHP tuning (April 2026 Best Practices)
ENV GOMEMLIMIT 850MiB

# Binary PHP Extension Installer
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install essential runtime tools only (curl for healthcheck)
RUN apk add --no-cache curl

# Copy Composer binary from stage 1 for universal use
COPY --from=composer-builder /usr/bin/composer /usr/bin/composer

# Fast Binary Extension Installation (Adding cache bust)
# [CACHE_BUST_2026_01]
RUN install-php-extensions \
    pdo_mysql \
    bcmath \
    sockets \
    exif \
    zip \
    gd \
    intl \
    opcache \
    pcntl \
    redis

# Load OPcache config for all stages
COPY docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini

# Stage 4: Development Environment
FROM base AS dev
# Install Node, Git, and Chokidar for dev stage
RUN apk add --no-cache nodejs npm git && \
    npm install -g chokidar

# Copy dependencies and application
COPY --from=composer-builder /app/vendor ./vendor
COPY . .
# Copy built assets
COPY --from=node-builder /app/public/build ./public/build

# Setup Directories and Permissions
RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Run discovery
RUN php artisan package:discover --ansi && \
    (php artisan octane:install --server=frankenphp || true)

# Entrypoint setup
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["docker-entrypoint.sh"]

EXPOSE 80
CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=80", "--admin-port=2019", "--watch"]

# Stage 5: Production Environment
FROM base AS prod
# Copy PHP dependencies and application
COPY --from=composer-builder /app/vendor ./vendor
COPY . .
# Copy built assets
COPY --from=node-builder /app/public/build ./public/build

# Recache/Optimize for Production
# We move these to entrypoint to ensure connectivity to the database is available
RUN composer dump-autoload --optimize --classmap-authoritative
#    php artisan optimize && \
#    php artisan filament:optimize && \
#    php artisan icons:cache

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Healthcheck
HEALTHCHECK --interval=30s --timeout=5s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/up || exit 1

# Entrypoint setup
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["docker-entrypoint.sh"]

EXPOSE 80
CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=80", "--admin-port=2019"]
