# Use the official FrankenPHP image with PHP 8.3
FROM dunglas/frankenphp:latest-alpine AS base

# Set working directory
WORKDIR /app

# FrankenPHP specific settings
# See https://frankenphp.dev/docs/worker/
ENV FRANKENPHP_CONFIG "worker /app/public/index.php"

# Install system dependencies for PHP extensions
# Add any other dependencies your app might need
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    linux-headers \
    icu-dev \
    libexif-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev

# Install PHP extensions required by Laravel and Redis
RUN docker-php-ext-install \
    pdo_mysql \
    bcmath \
    sockets \
    exif \
    zip \
    gd \
    intl

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --- Development Stage ---
FROM base AS dev

# Copy composer files and install dependencies
# This is done in a separate step to leverage Docker's layer caching
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-scripts --no-progress

# Copy the rest of the application code
COPY . .

COPY docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini

# Create storage and bootstrap cache directories for Laravel
# This ensures composer scripts and the app have a place to write files.
RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache

RUN composer dump-autoload --optimize --classmap-authoritative --no-dev


# --- THIS IS THE NEW OPTIMIZATION STEP ---
# Build all the application caches. This makes boot-up much faster.
RUN php artisan package:discover --ansi && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan filament:cache-components

# THIS IS THE NEW LINE
# Copy our custom Caddyfile to the correct location
COPY docker/caddy/Caddyfile /etc/caddy/Caddyfile

# Set permissions for storage and bootstrap cache
# This MUST come after caching, as caching creates files.
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Create an entrypoint to run migrations on startup
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["docker-entrypoint.sh"]

# Expose port 80 for the web server
EXPOSE 80

# This command is what FrankenPHP will run
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
