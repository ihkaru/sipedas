#!/bin/sh
set -e

# Wait for database connection
echo "Waiting for database connection..."
until php -r "try { new PDO('mysql:host='.getenv('DB_HOST').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); exit(0); } catch (Throwable \$e) { exit(1); }" > /dev/null 2>&1; do
  echo "Database is unavailable - sleeping 2s"
  sleep 2
done

# Ensure storage link exists
if [ ! -L /app/public/storage ]; then
  echo "Creating storage symlink..."
  php artisan storage:link --force --no-interaction || true
fi

# Automatic migration on container startup (Ensures DB is ready on Coolify autodeploy)
echo "Running database migrations..."
php artisan migrate --force --no-interaction || true

# Production Caching & Framework Optimizations (Config, Routes, Views, Filament, Icons)
if [ "$APP_ENV" = "production" ]; then
  echo "Optimizing framework caches for production..."
  php artisan optimize || true
  php artisan filament:optimize || true
  php artisan icons:cache || true
fi

# Fast and targeted permissions (Avoid recursive chown on the entire uploads library)
echo "Ensuring runtime permissions..."
mkdir -p /app/storage/framework/sessions \
         /app/storage/framework/views \
         /app/storage/framework/cache \
         /app/storage/logs \
         /app/bootstrap/cache
chown -R www-data:www-data /app/storage/framework /app/storage/logs /app/bootstrap/cache 2>/dev/null || true
chmod -R 775 /app/storage/framework /app/storage/logs /app/bootstrap/cache 2>/dev/null || true

echo "System ready. Starting FrankenPHP / Octane."
exec "$@"
