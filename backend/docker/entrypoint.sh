#!/bin/sh
# ./backend/docker/entrypoint.sh

set -e

echo "Starting Laravel application..."

# Wait for database to be ready
echo "Waiting for database..."
while ! pg_isready -h postgres -p 5432 -U postgres; do
    echo "Database is unavailable - sleeping"
    sleep 1
done
echo "Database is ready!"

# Wait for Redis to be ready
echo "Waiting for Redis..."
while ! redis-cli -h redis ping; do
    echo "Redis is unavailable - sleeping"
    sleep 1
done
echo "Redis is ready!"

# Generate application key if not exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate app key
php artisan key:generate --no-interaction

# Clear and cache config
php artisan config:clear
php artisan config:cache

# Run migrations
php artisan migrate --force

# Seed the database (if needed)
php artisan db:seed --force

# Clear and cache routes
php artisan route:clear
php artisan route:cache

# Clear and cache views
php artisan view:clear
php artisan view:cache

# Create storage link
php artisan storage:link

# Set proper permissions
chmod -R 775 storage bootstrap/cache

echo "Laravel application is ready!"

# Execute the original command
exec "$@"