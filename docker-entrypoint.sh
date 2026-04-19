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
if ! php artisan migrate --force; then
  echo "WARNING: Initial migration failed. Attempting self-healing for known table collisions..."
  
  # Known migrations that might collision if DB was synced without migration logs
  KNOWN_CONFLICTS="2025_07_30_145447_create_honors_table 2025_07_31_102717_create_kemitraans_table 2025_08_17_051536_create_alokasi_honors_table 2025_09_03_111353_create_action_tokens_table 2026_04_17_121000_create_custom_pages_table"
  
  for migration in $KNOWN_CONFLICTS; do
    echo "Checking $migration..."
    # We use a simple tinker command to register the migration if it doesn't exist
    php artisan tinker --execute="if(\DB::table('migrations')->where('migration', '$migration')->doesntExist()) { \DB::table('migrations')->insert(['migration' => '$migration', 'batch' => 999]); echo 'Registered $migration'; }" || echo "Failed to register $migration, ignoring..."
  done

  # Try migrating again after healing
  echo "Retrying migration after self-healing..."
  php artisan migrate --force || echo "CRITICAL: Migrations still failing, but proceeding to start application to allow debugging..."
fi

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
