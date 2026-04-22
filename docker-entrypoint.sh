#!/bin/sh
set -e

# Wait for database connection (Exponential backoff would be better, but loop is standard)
echo "Waiting for database..."
until php artisan db:monitor > /dev/null 2>&1; do
  echo "Database is unavailable - sleeping"
  sleep 2
done

# Run migrations (Resilient Mode - April 2026)
echo "Running database migrations..."
php artisan migrate --force --no-interaction || {
    echo "WARNING: Migration failed. This usually happens when the database is out of sync with migration logs."
    echo "Attempting to continue anyway to allow the application to start..."
}

# Recache everything for maximum performance in Worker Mode
# This ensures that even if files are missing from the image root,
# the app has its internal maps ready.
echo "Optimizing application for Production (April 2026)..."
php artisan optimize || echo "Optimization failed, ignoring..."
php artisan filament:optimize || echo "Filament optimization skipped"
php artisan icons:cache || echo "Icons cache skipped"

# Ensure storage link exists
php artisan storage:link --force

# Robust Permission Handling
echo "Setting correct permissions..."
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

echo "System ready. Starting FrankenPHP in Worker Mode."
exec "$@"
