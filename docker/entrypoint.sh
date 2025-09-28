#!/bin/bash

# Wait for database to be ready
echo "Waiting for database connection..."
while ! nc -z database 3306; do
  sleep 1
done
echo "Database is ready!"

# Generate application key if it doesn't exist
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate application key
php artisan key:generate --force

# Run database migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache
exec "$@"
