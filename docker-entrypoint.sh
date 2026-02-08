#!/bin/bash
set -e

echo "🚀 ClockWise - Starting deployment..."

# Ensure .env exists
if [ ! -f /var/www/html/.env ]; then
    echo "⚙️  Creating .env file..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "⚙️  Generating application key..."
    php artisan key:generate --force
else
    # Write the Render-provided APP_KEY into .env so config:cache picks it up
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" /var/www/html/.env
fi

# Cache configuration for performance
echo "⚙️  Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "📦 Running database migrations..."
php artisan migrate --force

# Seed if database is empty (first deploy)
USERS_COUNT=$(php artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null || echo "0")
if [ "$USERS_COUNT" = "0" ]; then
    echo "🌱 First deploy detected — seeding database..."
    php artisan db:seed --force
fi

# Create storage link
php artisan storage:link 2>/dev/null || true

echo "✅ ClockWise is ready! Starting Apache..."

# Start Apache
exec apache2-foreground
