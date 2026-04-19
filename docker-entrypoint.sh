#!/bin/sh
set -e

# Wait for database connection (Exponential backoff would be better, but loop is standard)
echo "Waiting for database..."
until php artisan db:monitor > /dev/null 2>&1; do
  echo "Database is unavailable - sleeping"
  sleep 2
done

# Run migrations (Standard in some setups, but ensure it's idempotent)
# In high-availability production, migrations are usually run as a separate task.
# Here we keep it for simplicity but use --force
echo "Running database migrations..."
php artisan migrate --force

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
