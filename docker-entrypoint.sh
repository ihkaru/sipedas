#!/bin/sh
set -e

# ONLY run migrations at runtime.
# The --force flag is important for non-interactive environments.
# echo "Running database migrations..."
php artisan migrate:fresh --seed --force

php artisan package:discover --ansi
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize

# --- THIS IS THE CRUCIAL FIX ---
# Re-apply ownership to the www-data user after running commands as root.
# This ensures the web server can write to the storage and bootstrap directories.
echo "Setting correct permissions..."
chown -R www-data:www-data /app/storage /app/bootstrap/cache

echo "Permissions set. Starting FrankenPHP."
# Execute the main command (from the Dockerfile's CMD)
# This starts the FrankenPHP server.
exec "$@"
