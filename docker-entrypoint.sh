#!/bin/sh
set -e

# Wait for database connection
echo "Waiting for database..."
until php -r "try { new PDO('mysql:host='.getenv('DB_HOST').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); exit(0); } catch (Throwable \$e) { exit(1); }" > /dev/null 2>&1; do
  echo "Database is unavailable - sleeping"
  sleep 1
done

# Ensure storage link exists
if [ ! -e /app/public/storage ]; then
  php artisan storage:link --force --no-interaction || true
fi

# Automatic migration on container startup (Ensures DB is ready on Coolify autodeploy)
echo "Running database migrations..."
php artisan migrate --force --no-interaction || true

# Robust & Cross-Platform Permission Handling
echo "Setting correct permissions..."
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache 2>/dev/null || true
chmod -R 775 /app/storage /app/bootstrap/cache 2>/dev/null || true

echo "System ready. Starting FrankenPHP / Octane."
exec "$@"
