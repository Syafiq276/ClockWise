#!/bin/bash
set -e

# Set default port if not provided
export PORT=${PORT:-10000}
echo "==> Starting ClockWise on port $PORT"

# Generate nginx config with correct port (use sed instead of envsubst)
sed "s/__PORT__/$PORT/g" /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

echo "==> Running database migrations..."
php artisan migrate --force || true

echo "==> Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "==> Starting PHP-FPM..."
php-fpm -D

# Wait for PHP-FPM to start
sleep 2

echo "==> Starting Nginx on port $PORT..."
nginx -g "daemon off;"
