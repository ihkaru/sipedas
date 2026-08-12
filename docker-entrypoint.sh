#!/bin/sh
set -e

# Wait for database connection
echo "Waiting for database..."
until php -r "try { new PDO('mysql:host='.getenv('DB_HOST').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); exit(0); } catch (Throwable \$e) { exit(1); }" > /dev/null 2>&1; do
  echo "Database is unavailable - sleeping"
  sleep 2
done

# Ensure storage link exists
if [ ! -e /app/public/storage ]; then
  php artisan storage:link --force --no-interaction || true
fi

# Robust Permission Handling
echo "Setting correct permissions..."
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

echo "System ready. Starting FrankenPHP in Worker Mode."
exec "$@"
