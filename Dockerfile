# syntax=docker/dockerfile:1.7

# Stage 1: Build PHP dependencies (Production Lean & Cached)
FROM composer:2 AS composer-builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/cache \
    composer install \
    --no-dev \
    --prefer-dist \
    --no-scripts \
    --no-progress \
    --no-interaction \
    --ignore-platform-reqs

# Stage 2: Build frontend assets
FROM node:22-slim AS node-builder
WORKDIR /app
COPY package.json package-lock.json* ./
RUN --mount=type=cache,target=/root/.npm \
    npm ci --prefer-offline --no-audit
COPY . .
# Only copy filament vendor files required by Tailwind content scanning
COPY --from=composer-builder /app/vendor/filament ./vendor/filament
RUN npm run build

# Stage 3: PHP Base Runtime (Debian Bookworm for Glibc JIT & FrankenPHP Production Stability)
FROM dunglas/frankenphp:php8.4-bookworm AS base
WORKDIR /app

# Core FrankenPHP tuning (Production Best Practices)
ENV GOMEMLIMIT=850MiB

# Install system essentials (curl for healthcheck)
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Binary PHP Extension Installer
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Fast Binary Extension Installation
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

# Load OPcache config and customized Octane Caddyfile
COPY docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/caddy/Caddyfile /etc/caddy/Caddyfile

# Copy Composer binary from stage 1 for universal use
COPY --from=composer-builder /usr/bin/composer /usr/bin/composer

# Stage 4: Development Environment
FROM base AS dev
RUN apt-get update && apt-get install -y --no-install-recommends \
    nodejs \
    npm \
    git && \
    npm install -g chokidar && \
    rm -rf /var/lib/apt/lists/*

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
ENV APP_ENV=production \
    APP_DEBUG=false

# Copy PHP dependencies (clean, production only)
COPY --from=composer-builder /app/vendor ./vendor
COPY . .
# Copy built assets
COPY --from=node-builder /app/public/build ./public/build

# Optimize autoloader for production without dev dependencies
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

# Set permissions for runtime writable directories
RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache && \
    chown -R www-data:www-data storage/framework storage/logs bootstrap/cache && \
    chmod -R 775 storage/framework storage/logs bootstrap/cache

# Healthcheck with safe start-period for DB wait + migration
HEALTHCHECK --interval=20s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -f http://localhost/up || exit 1

# Entrypoint setup
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["docker-entrypoint.sh"]

EXPOSE 80
CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=80", "--admin-port=2019", "--caddyfile=/etc/caddy/Caddyfile", "--max-requests=1000"]
