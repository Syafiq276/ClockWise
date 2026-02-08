#!/bin/bash
set -e

echo "🚀 ClockWise - Starting deployment..."

# Ensure .env exists
if [ ! -f /var/www/html/.env ]; then
    echo "⚙️  Creating .env file..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Write ALL runtime environment variables into .env
# so that config:cache picks up Render's env vars (not .env.example defaults)
echo "⚙️  Syncing environment variables..."
ENV_FILE=/var/www/html/.env

# Auto-detect DB_CONNECTION from DATABASE_URL if not set
if [ -z "$DB_CONNECTION" ] && [ -n "$DATABASE_URL" ]; then
    export DB_CONNECTION=pgsql
fi
for var in APP_NAME APP_ENV APP_DEBUG APP_URL APP_KEY \
           DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD DATABASE_URL \
           LOG_CHANNEL SESSION_DRIVER CACHE_STORE QUEUE_CONNECTION \
           MAIL_MAILER MAIL_HOST MAIL_PORT MAIL_USERNAME MAIL_PASSWORD MAIL_FROM_ADDRESS; do
    val=$(printenv "$var" 2>/dev/null || true)
    if [ -n "$val" ]; then
        if grep -q "^${var}=" "$ENV_FILE"; then
            sed -i "s|^${var}=.*|${var}=${val}|" "$ENV_FILE"
        else
            echo "${var}=${val}" >> "$ENV_FILE"
        fi
    fi
done

# Generate app key if not set
if ! grep -q "^APP_KEY=base64:" "$ENV_FILE"; then
    echo "⚙️  Generating application key..."
    php artisan key:generate --force
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
